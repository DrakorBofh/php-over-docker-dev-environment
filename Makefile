composeDir=docker
composeFile=${composeDir}/docker-compose_cli.yml
projectDir=/var/www/html/${projectName}/
projectName=php-env-example
servicePhp=php-cliserver
serviceRabbitMQ=rabbitmq
serviceDB=mysql

dockerComposeBase=docker-compose --project-directory $(composeDir) -f $(composeFile)
dockerComposeBasePHP=$(dockerComposeBase) run --user "$(shell id -u):$(shell id -g)" --workdir="$(projectDir)" $(servicePhp)
dockerComposeBaseRabbitMQ=$(dockerComposeBase) exec --user "$(shell id -u):$(shell id -g)" $(serviceRabbitMQ)
dockerComposeBaseDB=$(dockerComposeBase) exec --user "$(shell id -u):$(shell id -g)" $(serviceDB)

help:
	@echo -e "\e[1m\e[31mUsage:\e[0m"
	@echo " make [command] [Option]"
	@echo ""
	@echo -e "\e[1m\e[31mAvailable commands:\e[0m"
	@echo -e " \e[1m\e[31mDocker\e[0m"
	@echo -e "  \e[1m\e[32mup\e[0m" "                                    Run current docker composer configuracion if it's already builded."
	@echo -e "  \e[1m\e[32mbuild\e[0m" "                                 Build current docker composer configuracion, dropping all old instances."
	@echo -e "  \e[1m\e[32mrun\e[0m" "                                   Run the public/index.php in a new php service instance."
	@echo -e "  \e[1m\e[32mlist-containers\e[0m" "                       List the running containers."
	@echo -e "  \e[1m\e[32mdown\e[0m" "                                  Stop all running containers."
	@echo -e "  \e[1m\e[32mprompt\e[0m" "                                Go to the php container shell."
	@echo -e "  \e[1m\e[32mmysql-prompt\e[0m" "                          Go to the mysql container shell."

	@echo -e " \e[1m\e[31mComposer\e[0m"
	@echo -e "  \e[1m\e[32mcomposer-require\e[0m" "                      "
	@echo -e "  \e[1m\e[32mcomposer-require-dev\e[0m" "                  "
	@echo -e "  \e[1m\e[32mcomposer-install\e[0m" "                      "
	@echo -e "  \e[1m\e[32mcomposer-update\e[0m" "                       "
	@echo -e "  \e[1m\e[32mcomposer-dump-autoload\e[0m" "                "

	@echo -e " \e[1m\e[31mDoctrine\e[0m"
	@echo -e "  \e[1m\e[32mdoctrine-scheme-create\e[0m" "                Run './vendor/bin/doctrine orm:schema-tool:create' on php service"
	@echo -e "  \e[1m\e[32mdoctrine-scheme-drop\e[0m" "                  Run './vendor/bin/doctrine orm:schema-tool:drop --force' on php service"
	@echo -e "  \e[1m\e[32mdoctrine-scheme-update:\e[0m" "               Run './vendor/bin/doctrine orm:schema-tool:update --force --dump-sql' on php service"

	@echo -e " \e[1m\e[31mRabbitMQ\e[0m"
	@echo -e "  \e[1m\e[32mrabbitmq-list-queues\e[0m" "                  List current queues on rabbitmq session"

	@echo -e " \e[1m\e[31mTests and code validation\e[0m"
	@echo -e "  \e[1m\e[32mskeleton-validate\e[0m" "                     Validate project structure"
	@echo -e "  \e[1m\e[32mgenerate-docs\e[0m" "                         Generate htmls docs from phpdoc source code"
	@echo -e "  \e[1m\e[32mtest\e[0m" "                                  Run PHPUnit tests. Use option FILE=<filename> to only check that file/directory"
	@echo -e "  \e[1m\e[32mstan\e[0m" "                                  Run PHPStan. Use option FILE=<filename> to only check that file/directory"
	@echo -e "  \e[1m\e[32mcs-fixer\e[0m" "                              Run cs-fixer. Use option FILE=<filename> to only check that file/directory"
	@echo -e "  \e[1m\e[32mtail-logs\e[0m" "                             Show log tails on php service"
	@echo -e "  \e[1m\e[32mtest-rabbitmq-callbacks\e[0m" "               Run 'tests/AMQP/AMQPSendTester.php'"

################################################################################
### DOCKER
################################################################################

up:
	docker-compose --project-directory ${composeDir} -f ${composeFile} up -d

build:
	docker-compose --project-directory ${composeDir} -f ${composeFile} rm -vsf
	docker-compose --project-directory ${composeDir} -f ${composeFile} down -v --remove-orphans
	docker-compose --project-directory ${composeDir} -f ${composeFile} build
	docker-compose --project-directory ${composeDir} -f ${composeFile} up -d
down:
	docker-compose --project-directory ${composeDir} -f ${composeFile} down

run:
	$(dockerComposeBasePHP) php ./public/index.php

list-containers:
	docker-compose --project-directory ${composeDir} -f ${composeFile} ps

prompt:
	$(dockerComposeBasePHP) bash

mysql-prompt:
	$(dockerComposeBaseDB) bash

################################################################################
### Composer
################################################################################

composer-require:
	$(dockerComposeBasePHP) composer require

composer-require-dev:
	$(dockerComposeBasePHP) composer require --dev

composer-install:
	$(dockerComposeBasePHP) composer install

composer-update:
	$(dockerComposeBasePHP) composer update

composer-dump-autoload:
	$(dockerComposeBasePHP) composer dump-autoload

################################################################################
### Doctrine
################################################################################

doctrine-scheme-create:
	$(dockerComposeBasePHP) ./vendor/bin/doctrine orm:schema-tool:create

doctrine-scheme-drop:
	$(dockerComposeBasePHP) ./vendor/bin/doctrine orm:schema-tool:drop --force

doctrine-scheme-update:
	$(dockerComposeBasePHP) ./vendor/bin/doctrine orm:schema-tool:update --force --dump-sql


################################################################################
### RabbitMQ
################################################################################

rabbitmq-list-queues:
	$(dockerComposeBaseRabbitMQ) rabbitmqctl list_queues


################################################################################
### Tests, code validation and documentation
################################################################################

skeleton-validate:
	$(dockerComposeBasePHP) php ./vendor/bin/pds-skeleton validate

generate-docs:
	$(dockerComposeBasePHP) doxygen docs/Doxyfile

test:
ifndef FILE
	$(dockerComposeBasePHP) ./vendor/bin/phpunit ./tests/
else
	$(dockerComposeBasePHP) ./vendor/bin/phpunit ./tests/ --group $(FILE)
endif

stan:
ifndef FILE
	$(dockerComposeBasePHP) ./vendor/bin/phpstan analyse -c phpstan.neon --level 7
else
	$(dockerComposeBasePHP) ./vendor/bin/phpstan analyse $(FILE) --level 7
endif

cs-fixer:
ifndef FILE
	$(dockerComposeBasePHP) ./vendor/bin/php-cs-fixer fix -v .
else
	$(dockerComposeBasePHP) ./vendor/bin/php-cs-fixer fix -v $(FILE)
endif

tail-logs:
	docker-compose --project-directory ${composeDir} -f ${composeFile} logs -f ${servicePhp}

test-rabbitmq-callbacks:
	$(dockerComposeBasePHP) php ./tests/AMQP/AMQPSendTester.php
