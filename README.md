# About

This is an example of how we can use docker to create a development environment, and use it to CI and CD.\
You can see the explanation about how I create this here in my site [here](http://fernando.ristano.com/blog/php-docker-env).

This approach aims to get quickly an environmet to develop, test and deploy without take care about the developer work stack.

# Dependencies

>>>
You giveme a **Docker** instance, a **docker-compose**(>=1.23) and **Makefile**, and I wil build you a dev environment


-- _*me*_
>>>

# Components

In this example you will have a microservice called **Base** (Naming is the hardest part in all project right?) who is in charge of the **Users** management.\
This microservice use RabbitMQ to comunicate with others microservices. To allow to test how the microservice response over RabbitMQ instance, you will have a little code available.

## Base microservice

This little microservice will use a **Mysql** database, **Doctrine** as **ORM** to manage it, **klogger** with a wrapper to the logs and **toml** files to configuration info.
I will go deeper in this microservice in an extra section, if you have curiosity about it.

You will found the general configuration file here: `config/config.toml`.

## RabbitMQ

As message broker, I use **RabbitMQ**, in mode **RPC**. Aaaand, nothing more to say here, all is very simple :).

## Other tools

As I said before, the main idea in this project is make all dev environment as easy and quickly as posible, but give the tools to write good and testeable code. So, I setted up a couple of extra tools.

You will see **PHPUnit** for the unit tests, **PHPStan** for the static code analysis and **PHP-CS-Fixer** to follow the coding standars.

I will explain how to use it on dev time as CI time bellow, don't worry.

# Time for the action!

All you need to know is in the makefile. To see all his can do for us just run

`make help` or simply `make`

and you will see something like this, but with more colors:

```
make [command][Option]

Available commands:
 Docker
  up                                     Run current docker composer configuracion if it's already builded.
  build                                  Build current docker composer configuracion, dropping all old instances.
  run                                    Run the public/index.php in a new php service instance.
  list-containers                        List the running containers.
  down                                   Stop all running containers.
  prompt                                 Go to the php container shell.
  mysql-prompt                           Go to the mysql container shell.
 Composer
  composer-require
  composer-require-dev
  composer-install
  composer-update
  composer-dump-autoload
 Doctrine
  doctrine-scheme-create                 Run './vendor/bin/doctrine orm:schema-tool:create' on php service
  doctrine-scheme-drop                   Run './vendor/bin/doctrine orm:schema-tool:drop --force' on php service
  doctrine-scheme-update:                Run './vendor/bin/doctrine orm:schema-tool:update --force --dump-sql' on php service
 RabbitMQ
  rabbitmq-list-queues                   List current queues on rabbitmq session
 Tests and code validation
  skeleton-validate                      Validate project structure
  generate-docs                          Generate htmls docs from phpdoc source code
  test                                   Run PHPUnit tests. Use option FILE=<filename> to only check that file/directory
  stan                                   Run PHPStan. Use option FILE=<filename> to only check that file/directory
  cs-fixer                               Run cs-fixer. Use option FILE=<filename> to only check that file/directory
  tail-logs                              Show log tails on php service
  test-rabbitmq-callbacks                Run 'tests/AMQP/AMQPSendTester.php'

```

As you can see, there are a command for all generic task involved in development as som CI tasks.

## Docker stuff

The first thing we need to do, is build the containers and run them. So:

`make build`

And after all pulls and stuffs are done, we will have all containers running!

The command `make list-containers` will show us the list of current containers.

All of them are already connected and working together. Easy no?

The next time we will start docker, just need to use `make up` to have all ready to start to work, and when you done, just shutdown the containers with `make down`.

To complete the system installation, we need to run composers install: `make composers-install`

And voilá! we already have all the project running and ready to use/develop.

It't time to play around a little bit. We can run the test script. With this, we will be using the 3 containers: **PHP**, **MySql** and **RabbitMQ**.

`make test-rabbitmq-callbacks`

We can do some other stuffs directly on a containers instance. Per example, we can run `make prompt` and get a shell to run all the nasty comands you want.

From a CI task point of view, we can run some checks: `make stan` will run phpstan analysis


# Configuration
TODO

## Troubleshooting
If we use SELinux, we can have some issues with docker. We can disable temporary this running: `setenforce 0`
