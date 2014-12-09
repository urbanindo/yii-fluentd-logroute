<?php

/**
 * Fluentd Log Route class file.
 * 
 * @author Adinata <mail.dieend@gmail.com>
 * @since 2014.12.09
 */

namespace Urbanindo\Yii\Component\Logger;

use Fluent\Logger\FluentLogger;

/**
 * Log route using fluentd.
 *
 * @author Adinata <mail.dieend@gmail.com>
 * @since 2014.12.09
 */
class FluentdLogRoute extends CLogRoute
{
    /* @var string host name */
    protected $host;

    /* @var int port number. when you wanna use unix domain socket. set port to 0 */
    protected $port;

    /* @var string Various style transport: `tcp://localhost:port` */
    protected $transport;

    /* @var resource */
    protected $socket;

    /* @var PackerInterface */
    protected $packer;

    protected $tagFormat = 'yii.%l.%c';

    protected $options = array(
        "socket_timeout"     => FluentLogger::SOCKET_TIMEOUT,
        "connection_timeout" => FluentLogger::CONNECTION_TIMEOUT,
        "backoff_mode"       => FluentLogger::BACKOFF_TYPE_USLEEP,
        "backoff_base"       => 3,
        "usleep_wait"        => FluentLogger::USLEEP_WAIT,
        "persistent"         => false,
        "retry_socket"       => true,
    );

    private $_logger;

    /**
     * Initializes the route.
     * This method is invoked after the route is created by the route manager.
     */
    public function init()
    {
        $_logger = new FluentLogger($host, $port, $options, $packer);
    }

    /**
     * Processes log messages and sends them to specific destination.
     * Derived child classes must implement this method.
     * @param array $logs list of messages. Each array element represents one message
     * with the following structure:
     * array(
     *   [0] => message (string)
     *   [1] => level (string)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true));
     */
    protected function processLogs($logs) {
        $tag = $logs[2];
        $data = [
            'content' => $logs[0],
            'level' => $logs[1],
            'timestamp' => $logs[3],
            ];
        $_logger->post($tag,$data);
    }

    private function createTag($logs) {
        $ret = $tagFormat;
        str_replace("%c", $logs[2], $ret);
        str_replace("%l", $logs[1], $ret);
        return $ret;
    }
}
