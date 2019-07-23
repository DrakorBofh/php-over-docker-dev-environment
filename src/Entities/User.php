<?php
// src/entities/User.php
namespace Base\Entities;

require_once 'vendor/autoload.php';

use Base\Managers\DBManager as DBManager;

/**
 * @Entity @Table(name="Users")
 **/
class User
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    /** @Column(type="string") **/
    protected $name;

    public static function getClassFullName() : String
    {
        return 'Base\Entities\User';
    }

    public function __construct($name)
    {
        // $id setted up by doctrine
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function persist()
    {
        $entityManager = DBManager::getInstance()->getEntityManager();
        $entityManager->persist($this);
        $entityManager->flush();
    }

    public function toJsonString() : String
    {
        return "{ 'id': $this->id, 'name': '$this->name' }";
    }

    public function __toString()
    {
        return $this->toJsonString();
    }

    public function removeFromDB()
    {
        $entityManager = DBManager::getInstance()->getEntityManager();
        $entityManager->remove($this);
        $entityManager->flush();
    }
}
