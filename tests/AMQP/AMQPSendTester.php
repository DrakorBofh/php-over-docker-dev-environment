<?php
// tests/amqp/AMQPSendTester.php
namespace Base\Tests\AMQP;

require_once 'vendor/autoload.php';

/*
* This will be test al the rabbitmq messages consume callbacks
*/
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Yosymfony\Toml\Toml;
use Base\Managers\AMPQManager;
use Base\Networking\RPCResponse;

use Base\Tests\AMQP\AMQPRpcClientUser;

$config = Toml::ParseFile('config/config.toml');

$connection = new AMQPStreamConnection(
    $config['rabbitmq']['host'],
    $config['rabbitmq']['port'],
    $config['rabbitmq']['user'],
    $config['rabbitmq']['password']
);
$channel = $connection->channel();

////////////////////////////////////////////
// General example
////////////////////////////////////////////

$rpcClient = new AMQPRpcClientUser($channel);
$rpcClient->setTimeout(2);

echo 'Send createUser' , "\n";
$rpcClient->createUser(AMPQManager::QUEUE_NAME, 'createUser', 'juan');
echo 'Send editUser' , "\n";
$rpcClient->editUser(AMPQManager::QUEUE_NAME, 'editUser', 1, 'pedro');
echo 'Send getUserById' , "\n";
$rpcClient->getUserById(AMPQManager::QUEUE_NAME, 'getUserById', 1);
echo 'Send removeUser' , "\n";
$rpcClient->removeUser(AMPQManager::QUEUE_NAME, 'removeUser', 1);
echo 'Send getUserList' , "\n";
$rpcClient->getUserList(AMPQManager::QUEUE_NAME, 'getUserList');

echo "Waiting for replies…\n";
try {
    $replies = $rpcClient->getReplies();
    var_dump($replies);
} catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
    echo 'Error: ', $e->getMessage(), "\n";
}
