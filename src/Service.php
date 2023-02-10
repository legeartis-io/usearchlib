<?php

namespace Legeartis\UnifiedSearch;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Response;
use Legeartis\UnifiedSearch\exceptions\AccessDeniedException;
use Legeartis\UnifiedSearch\exceptions\BadRequestException;
use Legeartis\UnifiedSearch\exceptions\ExecutionException;
use Legeartis\UnifiedSearch\exceptions\NotFoundException;
use Legeartis\UnifiedSearch\exceptions\USException;

class Service
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @throws GuzzleException
     * @throws BadRequestException
     * @throws AccessDeniedException
     * @throws NotFoundException
     * @throws ExecutionException
     * @throws USException
     */
    protected function makeRequest(?string $returnType, string $method, string $controller, string $action = '', $id = null, array $params = [], bool $cancel = false, array $body = null, string $filePath = null, string $fileName = "")
    {
        try {
            $request = new Request($returnType, $method, $controller, $action, $id, $params, $cancel, $body, $filePath, $fileName);
            return $this->query($request);
        } catch (ServerException | ClientException $ex) {
            $serverJson = (string)$ex->getResponse()->getBody();
            $error = json_decode($serverJson, true);

            if ($error) {
                switch ($ex->getCode()) {
                    case 400:
                        throw new BadRequestException($error);
                    case 403:
                        throw new AccessDeniedException($error);
                    case 404:
                        throw new NotFoundException($error);
                    case 500:
                        throw new ExecutionException($error);
                }
                throw new USException($error);
            } else {
                throw $ex;
            }
        }
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param $str
     *
     * @return array|string|string[]
     */
    private function safeStr($str)
    {
        return str_replace('&', '%26', $str);
    }

    protected function getUrl(Request $request)
    {
        $params = '';

        foreach ($request->getParams() as $key => $param) {
            if (is_array($param)) {
                foreach ($param as $itemKey => $item) {
                    $params .= $this->safeStr($key) . '=' . $this->safeStr($item) . '&';
                }
            } else {
                $params .= $this->safeStr($key) . '=' . $this->safeStr($param) . '&';
            }
        }

        $url = '/V4/public/'
            . urlencode($request->getController())
            . ($request->getAction() ? '/' . urlencode($request->getAction()) : '')
            . ($request->getId() ? ('/' . urlencode($request->getId())) : '')
            . ($request->isCancel() ? '/cancel' : '')
            . ($params ? '?' . $params : '');

        return $url;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    protected function query(Request $request)
    {
        $client = new Client();

        $url = $this->config->getServiceUrl() . $this->getUrl($request);
        $options = [
            'auth' => [
                $this->config->getLogin(), $this->config->getPassword()
            ],
            'json' => $request->getBody()
        ];

        $connectionTimeout = $this->getConfig()->getConnectTimeout();
        if (!is_null($connectionTimeout)) {
            $options['connect_timeout'] = $connectionTimeout;
        }

        $timeout = $this->getConfig()->getTimeout();
        if (!is_null($timeout)) {
            $options['timeout'] = $timeout;
        }

        if ($file = $request->getFilePath()) {
            unset($options['json']);

            $options['multipart'] = [
                [
                    'name' => 'offersFile',
                    'contents' => fopen($file, 'r'),
                    'filename' => $request->getFileName(),
                    'headers' => [
                        'Authorization' => $this->config->getLogin() . ' ' . $this->config->getPassword()
                    ]
                ]
            ];
        }

        $response = $client->request($request->getMethod(), $url, $options);

        return $this->getResponseObject($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return mixed
     * @throws Exception
     */
    protected function getResponseObject(Request $request, Response $response)
    {
        $returnType = $request->getReturnType();
        if ($returnType != null) {
            $data = json_decode((string)$response->getBody(), true);
            return new $returnType($data);
        } elseif ($returnType == null) {
            return (string)$response->getBody();
        } else {
            throw new Exception('Unsupported object type');
        }
    }

}