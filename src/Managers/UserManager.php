<?php
// UserManager.php
namespace Base\Managers;

require_once 'vendor/autoload.php';

use Base\Managers\DBManager;
use Base\Entities\User;

class UserManager
{
    public static function getUserById($id)
    {
        $entityManager = DBManager::getInstance()->getEntityManager();
        $user = $entityManager->find(User::getClassFullName(), $id);
        return $user;
    }

    public static function getUserList()
    {
        //TODO manage pagination, position and lenght
        $entityManager = DBManager::getInstance()->getEntityManager();
        $repository = $entityManager->getRepository(User::getClassFullName());
        $users = $repository->findAll();

        return $users;
    }
}
