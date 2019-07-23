<?php
// src/bootstrap.php
namespace Base;

require_once 'vendor/autoload.php';

use Yosymfony\Toml\Toml;
use Base\Managers\AMPQManager;
use Base\Managers\DBManager;
use Base\Helpers\Logger;

class Bootstrap
{
    public static function initDB($config)
    {
        // Create DB connection
        DBManager::getInstance()->createConnection($config);
    }

    public static function initAMPQ($config)
    {
        // Create AMPQ connection
        AMPQManager::getInstance()->createConnection($config);

        // Start listening
        AMPQManager::getInstance()->startListening();
    }

    public static function initByDoctrineConsole()
    {
        $globalConfig = Toml::ParseFile('config/config.toml');
        Bootstrap::initDB($globalConfig['database']);
    }

    public static function completeInit()
    {
        $globalConfig = Toml::ParseFile('config/config.toml');
        Logger::getInstance()->init($globalConfig['logger']['path'], $globalConfig['logger']['debug'], 'Base');
        Logger::logInfo("******************************************************");
        Logger::logInfo("******* New Instance of Base started ***************");
        Logger::logInfo("******************************************************");
        Bootstrap::initDB($globalConfig['database']);
        Bootstrap::initAMPQ($globalConfig['rabbitmq']);
    }
}
