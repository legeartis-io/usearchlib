<?php

namespace Legeartis\UnifiedSearch;

class Config
{
    private $login;

    private $password;

    private $serviceUrl = 'https://api.us.legeartis.io';

    private $timeout = 10;

    private $connect_timeout = 3;

    public function __construct($params)
    {
        $this->setParams($params);
    }

    /**
     * @return mixed
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param mixed $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getServiceUrl()
    {
        return $this->serviceUrl;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getConnectTimeout()
    {
        return $this->connect_timeout;
    }

    /**
     * @param mixed $connect_timeout
     */
    public function setConnectTimeout($connect_timeout): void
    {
        $this->connect_timeout = $connect_timeout;
    }

    /**
     * @param mixed $serviceUrl
     */
    public function setServiceUrl($serviceUrl)
    {
        $this->serviceUrl = $serviceUrl;
    }

    private function setParams($params) {
        foreach ($params as $name => $param) {
            if(method_exists($this,'set' . $name)) {
                $this->{'set' . $name}($param);
            }
        }
    }
}
