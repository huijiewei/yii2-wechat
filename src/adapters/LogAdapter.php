<?php
/**
 * Created by PhpStorm.
 * User: huijiewei
 * Date: 2018/6/11
 * Time: 11:45
 */

namespace huijiewei\wechat\adapters;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogAdapter implements LoggerInterface
{
    const CATEGORY = 'wechat';

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        \Yii::warning($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message, array $context = [])
    {
        \Yii::warning($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message, array $context = [])
    {
        \Yii::error($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message, array $context = [])
    {
        \Yii::error($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message, array $context = [])
    {
        \Yii::warning($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message, array $context = [])
    {
        \Yii::warning($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message, array $context = [])
    {
        \Yii::info($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message, array $context = [])
    {
        \Yii::debug($this->format($message, $context), static::CATEGORY);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $msg = $this->format($message, $context);

        switch ($level) {
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
            case LogLevel::ALERT:
                \Yii::warning($msg, static::CATEGORY);
                break;
            case LogLevel::INFO:
                \Yii::info($msg, static::CATEGORY);
                break;
            case LogLevel::DEBUG:
                \Yii::debug($msg, static::CATEGORY);
                break;
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
                \Yii::error($msg, static::CATEGORY);
                break;
        }
    }

    /**
     * @param $message
     * @param $context
     * @return string
     */
    private function format($message, $context)
    {
        $context = json_encode($context);
        return "$message : $context";
    }
}
