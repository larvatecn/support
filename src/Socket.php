<?php
/**
 * @copyright Copyright (c) 2018 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larvacent.com/
 * @license http://www.larvacent.com/license/
 */

namespace Larva\Support;

/**
 * Class Socket
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Socket extends BaseObject
{
    /**
     * @var bool 是否是持久连接
     */
    public $persistent = false;

    /**
     * @var string IP
     */
    public $host;

    /**
     * @var int 端口
     */
    public $port;

    /**
     * @var int 超时时间
     */
    public $timeout;

    /**
     * @var null Socket 连接句柄
     */
    private $connection = null;

    /**
     * @var bool Socket 连接状态
     */
    private $connected = false;

    /**
     * 连接Socket
     * @return bool
     */
    public function connect(): bool
    {
        if ($this->connection != null) {
            $this->disconnect();
        }

        if ($this->persistent == true) {
            $tmp = null;
            $this->connection = @pfsockopen($this->host, $this->port, $errNum, $errStr, $this->timeout);
        } else {
            $this->connection = fsockopen($this->host, $this->port, $errNum, $errStr, $this->timeout);
        }

        if (!empty ($errNum) || !empty ($errStr)) {
            $this->error($errStr, $errNum);
        }
        $this->connected = is_resource($this->connection);
        return $this->connected;
    }

    /**
     * 错误信息
     * @param string $errStr
     * @param int $errNum
     */
    public function error(string $errStr, int $errNum)
    {

    }

    /**
     * 向流写入数据
     * @param string $data
     * @return bool|int
     */
    public function write(string $data)
    {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        return fwrite($this->connection, $data, strlen($data));
    }

    /**
     * 从流读取广告
     * @param int $length
     * @return bool|string
     */
    public function read($length = 1024)
    {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        if (!feof($this->connection)) {
            return fread($this->connection, $length);
        } else {
            return false;
        }
    }

    /**
     * 断开连接
     * @return bool
     */
    public function disconnect(): bool
    {
        if (!is_resource($this->connection)) {
            $this->connected = false;
            return true;
        }
        $this->connected = !fclose($this->connection);
        if (!$this->connected) {
            $this->connection = null;
        }
        return !$this->connected;
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
