<?php
// tests/amqp/AMQPRpcClient.php
namespace Base\Tests\AMQP;

require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

class AMQPRpcClient
{
    /**
    * @var \PhpAmqpLib\Channel\AMQPChannel
    */
    protected $channel;
    /**
     * @var int
     */
    protected $requests;
    /**
     * @var string[]
     */
    protected $replies;
    /**
     * @var string
     */
    protected $queueName;
    /**
     * @var int
     */
    protected $requestTimeout = null;


    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;

        list($this->queueName, , ) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            true
        );

        $this->requests = 0;
        $this->replies = array();
    }

    /**
     * Add request to be sent to RPC Server.
     *
     * @param string $messageBody
     * @param string $correlationId
     * @param string $routingKey
     */
    public function addRequest(string $messageBody, string $routingKey = '', string $correlationId = null)
    {
        if (empty($correlationId)) {
            $correlationId = uniqid();
        }

        //TODO check for existence of correlationId

        $message = new AMQPMessage(
            $messageBody,
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'content_type' => 'application/json',
                'correlation_id' => $correlationId,
                'reply_to' => $this->queueName
            ]
        );

        $this->channel
            ->basic_publish($message, '', $routingKey);
        $this->requests++;
    }

    /**
     * Get replies.
     *
     * @return array
     */
    public function getReplies()
    {
        $this->channel
            ->basic_consume(
                $this->queueName,
                $this->queueName,
                false,
                true,
                false,
                false,
                array($this, 'processMessage')
            );
        while (count($this->replies) < $this->requests) {
            $this->channel
                ->wait(null, false, $this->requestTimeout);
        }
        $this->channel
            ->basic_cancel($this->queueName);
        return $this->replies;
    }

    /**
     * @param AMQPMessage $message
     */
    public function processMessage(AMQPMessage $message)
    {
        $this->replies[$message->get('correlation_id')] = $message->body;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout(int $timeout)
    {
        $this->requestTimeout = $timeout;
    }
}
