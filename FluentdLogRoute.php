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
class FluentdLogRoute extends \CLogRoute
{
    /* @var string host name */
    protected $host = FluentLogger::DEFAULT_ADDRESS;

    /* @var int port number. when you wanna use unix domain socket. set port to 0 */
    protected $port = FluentLogger::DEFAULT_LISTEN_PORT;

    /* @var string Various style transport: `tcp://localhost:port` */
    protected $transport;

    /* @var resource */
    protected $socket;

    /* @var PackerInterface */
    protected $packer;

    protected $tagFormat = 'yii.%l.%c';
    
    public function setHost($host) {
        $this->host= $host;
    }
    public function setPort($port) {
        $this->port= $port;
    }

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
        $this->_logger = new FluentLogger($this->host, $this->port, $this->options, $this->packer);
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
        foreach ($logs as $log) {
            $tag = $this->createTag($log);
            $data = [
                'level' => $log[1],
                'timestamp' => $log[3],
                ];

            $data['content'] = $log[0];
            $this->_logger->post($tag,$data);
        }
    }

    private function createTag($log) {
        $ret = $this->tagFormat;
        $ret = str_replace("%c", $log[2], $ret);
        $ret = str_replace("%l", $log[1], $ret);
        return $ret;
    }
}
