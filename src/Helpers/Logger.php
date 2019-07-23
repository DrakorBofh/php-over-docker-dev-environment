<?php
// Helpers/Logger.php
namespace Base\Helpers;

require_once 'vendor/autoload.php';

use Base\Singleton;
use Psr\Log\LogLevel;

/**
* Singleton class to manage log output, based on klogger and Psr Log
*
* Example usage:
*   Logger::getInstance()->init('logs', true, 'log');
*
* @package  Base\Helpers
* @author   Fernando Anthony Ristaño <fernando.ristano@gmail.com>
* @version  $Revision: 1 $
* @access   public
*/
class Logger extends Singleton
{
    /**
     * @const
     */
    protected const ERROR_LEVEL = 0;
    /**
     * @const
     */
    protected const INFO_LEVEL = 1;
    /**
     * @const
     */
    protected const DEBUG_LEVEL = 2;

    /**
     * @const
     */
    protected const COLOR_FORMATS = array(
        // styles
        // italic and blink may not work depending of your terminal
        'bold' => "\033[1m%s\033[0m",
        'dark' => "\033[2m%s\033[0m",
        'italic' => "\033[3m%s\033[0m",
        'underline' => "\033[4m%s\033[0m",
        'blink' => "\033[5m%s\033[0m",
        'reverse' => "\033[7m%s\033[0m",
        'concealed' => "\033[8m%s\033[0m",
        // foreground colors
        'black' => "\033[30m%s\033[0m",
        'red' => "\033[31m%s\033[0m",
        'green' => "\033[32m%s\033[0m",
        'yellow' => "\033[33m%s\033[0m",
        'blue' => "\033[34m%s\033[0m",
        'magenta' => "\033[35m%s\033[0m",
        'cyan' => "\033[36m%s\033[0m",
        'white' => "\033[37m%s\033[0m",
        // background colors
        'bg_black' => "\033[40m%s\033[0m",
        'bg_red' => "\033[41m%s\033[0m",
        'bg_green' => "\033[42m%s\033[0m",
        'bg_yellow' => "\033[43m%s\033[0m",
        'bg_blue' => "\033[44m%s\033[0m",
        'bg_magenta' => "\033[45m%s\033[0m",
        'bg_cyan' => "\033[46m%s\033[0m",
        'bg_white' => "\033[47m%s\033[0m",
    );

    /**
     * @var \Katzgrau\KLogger\Logger
     */
    protected $logger;
    /**
     * @var bool
     */
    protected $debugMode;

    /**
     * Init Logger instance
     *
     * @param string $dir
     * @param boolean $debug
     */
    public function init(string $dir, bool $debug, string $prefix = 'log')
    {
        $this->logger = new \Katzgrau\KLogger\Logger(
            $dir,
            ($debug)? LogLevel::DEBUG: LogLevel::INFO,
            array('extension' => 'log', 'prefix' => $prefix.'_')
        );
        $this->debugMode = $debug;
    }

    /**
     * Get the Logger object
     *
     * @return \Katzgrau\KLogger\Logger
     */
    public function getLogger() : \Katzgrau\KLogger\Logger
    {
        return $this->logger;
    }

    /**
     * Check if the system is in debugMode
     *
     * @return bool
     */
    public function isDebugMode() : bool
    {
        return $this->debugMode;
    }

    /**
     * Show message in console if the system is in debugMode
     *
     * @param int $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    protected function showInConsole(int $level, string $message, array $context = array())
    {
        if ($this->isDebugMode()) {
            $label = "";
            $color = "";
            switch ($level) {
                case Logger::ERROR_LEVEL:
                    echo sprintf(self::COLOR_FORMATS['red'], '[ERROR]');
                    break;
                case Logger::INFO_LEVEL:
                    echo sprintf(self::COLOR_FORMATS['blue'], '[INFO]');
                    break;
                case Logger::DEBUG_LEVEL:
                    echo sprintf(self::COLOR_FORMATS['yellow'], '[DEBUG]');
                    break;

                default:
                    break;
            }

            echo " $message \n";
            if (!empty($context)) {
                echo "***********************\n";
                print_r($context);
                echo "***********************\n";
            }
        }
    }

    /**
     * Write a info log message.
     * If the logger is in debug mode, all we will show the output in console
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function logInfo(string $message, array $context = array())
    {
        $instance = Logger::getInstance();
        if ($instance != null) {
            $instance->getLogger()->info($message, $context);
            $instance->showInConsole(self::INFO_LEVEL, $message, $context);
        }
    }

    /**
     * Write an error log message.
     * If the logger is in debug mode, all we will show the output in console
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function logError(string $message, array $context = array())
    {
        $instance = Logger::getInstance();
        if ($instance != null) {
            $instance->getLogger()->error($message, $context);
            $instance->showInConsole(self::ERROR_LEVEL, $message, $context);
        }
    }

    /**
     * Write a debug log message.
     * If the logger is in debug mode, all we will show the output in console
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function logDebug(string $message, array $context = array())
    {
        $instance = Logger::getInstance();
        if ($instance != null) {
            $instance->getLogger()->debug($message, $context);
            $instance->showInConsole(self::DEBUG_LEVEL, $message, $context);
        }
    }
}
