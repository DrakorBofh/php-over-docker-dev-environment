<?php
// AMPQConsumerManager.php
namespace Base\AMPQConsumers;

require_once 'vendor/autoload.php';

use Base\AMPQConsumers\UserAMPQConsumer;
use Base\AMPQConsumers\AMPQConsumerCallbackConfig;
use Base\Helpers\Logger;
use Base\Networking\RPCResponse;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPInvalidArgumentException;

class AMPQConsumerManager
{
    protected $messageConsumers = array();

    public function __construct()
    {
        $this->addAMPQMessageConsumer(UserAMPQConsumer::getAMPQMessageConsumerList());
    }

    protected function addAMPQMessageConsumer(array $consumer)
    {
        foreach ($consumer as $command => $callback) {
            $this->messageConsumers[$command] = $callback;
        }
    }

    protected function sendReply($req, String $result)
    {
        $reply = new AMQPMessage(
            $result,
            array('correlation_id' => $req->get('correlation_id'))
        );
        $req->delivery_info['channel']
            ->basic_publish($reply, '', $req->get('reply_to'));
    }

    public function processAMPQMessage($req)
    {
        try {
            Logger::logDebug("[AMPQ] Received: $req->body");

            $req->delivery_info['channel']
                ->basic_ack($req->delivery_info['delivery_tag']);

            $data = json_decode($req->body, true);

            $response = null;
            if (isset($data['command']) && array_key_exists($data['command'], $this->messageConsumers)) {
                $callbackConfig = $this->messageConsumers[$data['command']];
                $callback = [$callbackConfig->className, $callbackConfig->method];
                if (is_callable($callback)) {
                    $params = array($data['params']);
                    $response = $callback(...$params);
                } else {
                    Logger::logError("[AMPQ] Function is not callable: $callbackConfig->className::$callbackConfig->method");
                    $this->sendReply($req, RPCResponse::withError(RPCResponse::ERROR_CODE_AMPQ_RUNTIME, 'Runtime error'));
                }
            } else {
                if (isset($data['command'])) {
                    Logger::logError("[AMPQ] There is not a command available".$data['command']);
                    $this->sendReply($req, RPCResponse::withError(RPCResponse::ERROR_CODE_AMPQ_RUNTIME, "Server can't handle the command: ".$data['command']));
                } else {
                    Logger::logError("[AMPQ] Client doen't sent the command");
                    $this->sendReply($req, RPCResponse::withError(RPCResponse::ERROR_CODE_AMPQ_INVALID_ARGUMENT, "Command empty!!"));
                }
            }

            Logger::logDebug("[AMPQ] Response: $response");
            $this->sendReply($req, $response);
        } catch (AMQPRuntimeException $exception) {
            $this->sendReply($req, RPCResponse::withError(RPCResponse::ERROR_CODE_AMPQ_RUNTIME, $exception->getMessage()));
        } catch (AMQPInvalidArgumentException $exception) {
            $this->sendReply($req, RPCResponse::withError(RPCResponse::ERROR_CODE_AMPQ_INVALID_ARGUMENT, $exception->getMessage()));
        }
        Logger::logDebug("[AMPQ] Done Message Process");
    }
}
