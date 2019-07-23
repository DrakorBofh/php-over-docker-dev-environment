<?php
// AMPQManager.php
namespace Base\Managers;

require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Base\Singleton;
use Base\AMPQConsumers\AMPQConsumerManager;
use Base\Helpers\Logger;

final class AMPQManager extends Singleton
{
    const QUEUE_NAME = 'base_queue';
    protected $connection = null;
    protected $channel = null;

    protected $consumerManager = null;

    public function createConnection($config)
    {
        $this->connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password']
        );

        $this->channel = $this->connection->channel();

        $this->consumerManager = new AMPQConsumerManager();

        $this->channel->queue_declare(self::QUEUE_NAME, false, true, false, false);
        $this->channel->basic_consume(self::QUEUE_NAME, '', false, false, false, false, array($this->consumerManager, 'processAMPQMessage'));

        Logger::logInfo("[AMPQ] Connection setup complete. Waiting for messages.");
    }

    public function startListening()
    {
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        $this->stopListening();
    }

    public function stopListening()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
