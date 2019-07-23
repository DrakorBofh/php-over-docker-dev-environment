<?php
// src/managers/DBManager.phps
namespace Base\Managers;

require_once 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Base\Singleton;

require_once 'src/Singleton.php';

class DBManager extends Singleton
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager = null;
    /**
     * @var array
     */
    protected $dbParams = null;

    /**
     * Create the Doctrine database connection.
     * Also create the database if not exists
     *
     * @param array $config
     * @param string $dir
     */
    public function createConnection(array $config, string $dir = "src")
    {
        try {
            $this->connectUsingDB($config['db_name'], $config, $dir);
        } catch (\Doctrine\DBAL\Exception\ConnectionException $e) {
            if ($e->getErrorCode() == 1049) {
                try {
                    $this->connectUsingDB('', $config, $dir);
                    $this->entityManager->getConnection()->executeUpdate('CREATE DATABASE '.$config['db_name'].' CHARACTER SET utf8 COLLATE utf8_general_ci');
                    $this->entityManager->getConnection()->close();
                    $this->connectUsingDB($config['db_name'], $config, $dir);
                } catch (\Exception $e) {
                    throw $e;
                }
            } else {
                throw $e;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Helper method that try a connection with a given database name.
     *
     * @param string $dbName
     * @param array $config
     * @param string $dir
     */
    protected function connectUsingDB(string $dbName, array $config, string $dir = "src")
    {
        $dbConfig = Setup::createAnnotationMetadataConfiguration(array($dir), $config['dev_mode']);

        $this->dbParams = array(
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'user' => $config['user'],
            'password' => $config['password'],
            'dbname' => empty($dbName)? null: $dbName,
        );

        try {
            $this->entityManager = EntityManager::create($this->dbParams, $dbConfig);
            $this->entityManager->getConnection()->connect();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the entityManager from current instance.
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() : \Doctrine\ORM\EntityManager
    {
        return $this->entityManager;
    }
}
