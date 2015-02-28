<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 2/28/15
 * Time: 9:44 PM
 */

namespace TomVerran\Stats\Storage\Database;


class MysqlConfiguration implements Configuration
{
    private $host;

    private $username;

    private $password;

    private $database;

    /**
     * Return a configuration array used by DatabaseStorage
     * @return array
     */
    public function toArray()
    {
        return [
            'driver' => 'mysqli',
            'user' => $this->username,
            'host' => $this->host,
            'port' => 3306,
            'password' => $this->password,
            'dbname' => $this->database
        ];
    }

    /**
     * @param mixed $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param mixed $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param mixed $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param mixed $database
     * @return $this
     */
    public function setDatabase( $database )
    {
        $this->database = $database;
        return $this;
    }
}