<?php
// RPCResponse.php
namespace Base\Networking;

require_once 'vendor/autoload.php';
use Base\Helpers\JsonHelper;

class RPCResponse
{
    const ERROR_CODE_UNKNOW = -1;
    const ERROR_CODE_NONE = 0;
    const ERROR_CODE_PARSE = 1;
    const ERROR_CODE_TIMEUP = 2;
    const ERROR_CODE_ARGUMENTS = 3;
    const ERROR_CODE_NOT_FOUND = 4;
    const ERROR_CODE_AMPQ = 10;
    const ERROR_CODE_AMPQ_RUNTIME = 11;
    const ERROR_CODE_AMPQ_INVALID_ARGUMENT = 12;

    protected $errorCode = 0;
    protected $errorMessage = null;
    protected $result = null;

    protected function __construct()
    {
    }

    public function setSuccess(string $result)
    {
        $this->errorCode = RPCResponse::ERROR_CODE_NONE;
        $this->errorMessage = null;
        $this->result = $result;
    }

    public function setError(int $errorCode, string $errorMessage = null, string $result = null)
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->result = $result;
    }

    public static function withSuccess(string $result)
    {
        $instance = new self();
        $instance->setSuccess($result);

        return $instance;
    }

    public static function withError(int $errorCode, string $errorMessage = null, string $result = null)
    {
        $instance = new self();
        $instance->setError($errorCode, $errorMessage, $result);

        return $instance;
    }

    public static function withJsonResponse($json)
    {
        $instance = new self();

        try {
            $data = JsonHelper::jsonDecodeAndValidate($json, true);
            $instance->errorCode = isset($data['errorCode'])? $data['errorCode'] : null;
            $instance->errorMessage = isset($data['errorMessage'])? $data['errorMessage'] : null;
            $instance->result = isset($data['result'])? $data['result'] : null;
        } catch (\Exception $e) {
            $instance->setError(RPCResponse::ERROR_CODE_PARSE, $e->getMessage());
        }

        return $instance;
    }

    public function isError() : bool
    {
        return ($this->errorCode != RPCResponse::ERROR_CODE_NONE);
    }

    public function toString() : String
    {
        $result = '{';
        if (!$this->isError()) {
            //"{'result':$this->result}"
            $result .= "'result':$this->result}";
        } else {
            //"{'error': $this->errorCode, 'errorMessage': $this->errorMessage, 'result': $this->result}";
            $result .= "'error': $this->errorCode";
            if (!empty($this->errorMessage)) {
                $result .= ", 'errorMessage': '$this->errorMessage'";
            }
            if (!empty($this->result)) {
                $result .= ", 'result': $this->result";
            }
            $result .= "}";
        }

        return $result;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
