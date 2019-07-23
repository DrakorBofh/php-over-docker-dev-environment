<?php
// tests/amqp/AMQPRpcClientUser.php
namespace Base\Tests\AMQP;

require_once 'vendor/autoload.php';

use Base\Tests\AMQP\AMQPRpcClient;

class AMQPRpcClientUser extends AMQPRpcClient
{
    public function createUser(string $routingKey, string $correlationId, string $name)
    {
        $data = [
            'command' => 'createUser',
            'params' => [
                'name' => $name
            ],
        ];
        $this->addRequest((string)json_encode($data), $routingKey, $correlationId);
    }

    public function removeUser(string $routingKey, string $correlationId, int $id)
    {
        $data = [
            'command' => 'removeUser',
            'params' => [
                'id' => $id
            ],
        ];
        $this->addRequest((string)json_encode($data), $routingKey, $correlationId);
    }

    public function editUser(string $routingKey, string $correlationId, int $id, string $name)
    {
        $data = [
            'command' => 'editUser',
            'params' => [
                'id' => $id,
                'name' => $name
            ],
        ];
        $this->addRequest((string)json_encode($data), $routingKey, $correlationId);
    }

    public function getUserById(string $routingKey, string $correlationId, int $id)
    {
        $data = [
            'command' => 'userById',
            'params' => [
                 'id' => $id
             ],
        ];
        $this->addRequest((string)json_encode($data), $routingKey, $correlationId);
    }

    public function getUserList(string $routingKey, string $correlationId, int $page = 0, int $count = 10, string $sort = null)
    {
        $data = [
            'command' => 'userList',
            'params' => [
                'page' => $page,
                'count' => $count
            ],
        ];

        if ($sort != null) {
            $data['params']['sort'] = $sort;
        }
        $this->addRequest((string)json_encode($data), $routingKey, $correlationId);
    }
}
