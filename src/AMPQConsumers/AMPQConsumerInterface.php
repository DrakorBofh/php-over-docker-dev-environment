<?php
// AMPQConsumerInterface.php
namespace Base\AMPQConsumers;

require_once 'vendor/autoload.php';

interface AMPQConsumerInterface
{
    public static function getAMPQMessageConsumerList(): array;
}
