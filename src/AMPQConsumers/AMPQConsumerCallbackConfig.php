<?php
// AMPQConsumerCallbackConfig.php
namespace Base\AMPQConsumers;

require_once 'vendor/autoload.php';

class AMPQConsumerCallbackConfig
{
    public $className = '';
    public $method = '';

    public function __construct($className, $method)
    {
        $this->className = $className;
        $this->method = $method;
    }
}
