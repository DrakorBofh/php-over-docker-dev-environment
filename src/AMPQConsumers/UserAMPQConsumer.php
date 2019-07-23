<?php
// UserAMPQConsumer.php
namespace Base\AMPQConsumers;

require_once 'vendor/autoload.php';

use Base\AMPQConsumers\AMPQConsumerInterface;
use Base\AMPQConsumers\AMPQConsumerCallbackConfig;
use Base\Networking\RPCResponse;
use Base\Entities\User;
use Base\Managers\UserManager;
use Base\Helpers\Logger;

class UserAMPQConsumer implements AMPQConsumerInterface
{
    public static function createUser($params)
    {
        Logger::logDebug('callback createUser with params: ', $params);
        if (isset($params['name'])) {
            $user = new User($params['name']);
            try {
                $user->persist();
                return RPCResponse::withSuccess($user->toJsonString());
            } catch (\Doctrine\DBAL\DBALException $e) {
                Logger::logError('Error trying to persist a new user. ' . $e->getMessage());
                return RPCResponse::withError(RPCResponse::ERROR_CODE_UNKNOW, 'Error trying to persist a new user');
            }
        } else {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_ARGUMENTS, 'Name missing');
        }
    }

    public static function removeUser($params)
    {
        Logger::logDebug('callback removeUser with params: ', $params);
        //TODO check params values and throw a propper error

        if (!isset($params['id'])) {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_ARGUMENTS, 'Missing id argument');
        }
        $user = UserManager::getUserById($params['id']);
        if ($user != null) {
            try {
                $user->removeFromDB();
                return RPCResponse::withSuccess('Successfully removed user with id: '.$params['id']);
            } catch (\Doctrine\DBAL\DBALException $e) {
                Logger::logError('Error trying to persist a new user' . $e->getMessage());
                return RPCResponse::withError(RPCResponse::ERROR_CODE_UNKNOW, 'Error trying to persist a new user');
            }
        } else {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_NOT_FOUND, 'User not found');
        }
    }

    public static function editUser($params)
    {
        Logger::logDebug('callback editUser with params: ', $params);

        $error = '';
        if (!isset($params['id'])) {
            $error .= ' Id missing.';
        }
        if (!isset($params['name'])) {
            $error .= ' Name missing.';
        }

        if (!empty($error)) {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_ARGUMENTS, $error);
        }

        $user = UserManager::getUserById($params['id']);
        if ($user == null) {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_NOT_FOUND, 'User not found');
        }

        $user->setName($params['name']);

        try {
            $user->persist();
            return RPCResponse::withSuccess($user->toJsonString());
        } catch (\Doctrine\DBAL\DBALException $e) {
            Logger::logError('Error trying to persist the changes on user' . $e->getMessage());
            return RPCResponse::withError(RPCResponse::ERROR_CODE_UNKNOW, 'Error trying to persist the changes on user');
        }
    }

    public static function getUserInfoById($params)
    {
        Logger::logDebug('callback getUserInfoById with params: ', $params);
        if (!isset($params['id'])) {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_ARGUMENTS, 'Missing id argument');
        }
        $user = UserManager::getUserById($params['id']);
        if ($user != null) {
            return RPCResponse::withSuccess($user->toJsonString());
        } else {
            return RPCResponse::withError(RPCResponse::ERROR_CODE_NOT_FOUND, 'User not found');
        }
    }

    public static function getUserList($params)
    {
        Logger::logDebug('callback getUserList with params: ', $params);
        //TODO check params values and thrown a propper error message
        //TODO manage pagination and response lenght

        $users = UserManager::getUserList();
        $output = '[';
        foreach ($users as $user) {
            $output = $output . $user->toJsonString() . ', ';
        }

        $output = $output . ']';
        return RPCResponse::withSuccess($output);
    }

    public static function getAMPQMessageConsumerList(): array
    {
        return array(
            'createUser' => new AMPQConsumerCallbackConfig('Base\\AMPQConsumers\\UserAMPQConsumer', 'createUser'),
            'removeUser' => new AMPQConsumerCallbackConfig('Base\\AMPQConsumers\\UserAMPQConsumer', 'removeUser'),
            'editUser' => new AMPQConsumerCallbackConfig('Base\\AMPQConsumers\\UserAMPQConsumer', 'editUser'),
            'userById' => new AMPQConsumerCallbackConfig('Base\\AMPQConsumers\\UserAMPQConsumer', 'getUserInfoById'),
            'userList' => new AMPQConsumerCallbackConfig('Base\\AMPQConsumers\\UserAMPQConsumer', 'getUserList'),
        );
    }
}
