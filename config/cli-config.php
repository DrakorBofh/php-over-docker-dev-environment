<?php
// config/cli-config.php
use \Doctrine\ORM\Tools\Console\ConsoleRunner;
use CSBase\Managers\DBManager;
use CSBase\Bootstrap;

Bootstrap::initByDoctrineConsole();
$entityManager = DBManager::getInstance()->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);
