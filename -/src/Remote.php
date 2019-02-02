<?php namespace ewma\remoteCall;

class Remote
{
    public static $instances = [];

    /**
     * @return \ewma\remoteCall\Remote
     */
    public static function getInstance($serverName)
    {
        if (!isset(static::$instances[$serverName])) {
            $server = new self($serverName);

            static::$instances[$serverName] = $server;
        }

        return static::$instances[$serverName];
    }

    private $controller;

    private $ssl;

    private $key;

    private $host;

    private $handlerUrl;

    public function __construct($serverName)
    {
        $serversData = dataSets()->get('ewma/remoteCall:servers');

        if ($serverData = ap($serversData, $serverName)) {
            $scheme = ap($serverData, 'scheme') ?: 'http';

            $this->ssl = $scheme == 'https';

            $this->host = $serverData['host'];
            $handlerRoute = $serverData['route'];

            $this->handlerUrl = $scheme . '://' . path($this->host, $handlerRoute) . '/';
            $this->key = ap($serverData, 'key');
        }

        $this->controller = appc('\ewma\remoteCall~');
    }

    public function isCurrent()
    {
        return $this->host === app()->host;
    }

    public function async($path, $data = [])
    {
        if ($this->isCurrent()) {
            return appc()->async($path, $data);
        } else {
            return $this->performRemoteCall($path, $data, true);
        }
    }

    public function call($path, $data = [])
    {
        if ($this->isCurrent()) {
            return appc($path, $data);
        } else {
            return $this->performRemoteCall($path, $data);
        }
    }

    private function performRemoteCall($path, $data = [], $async = false)
    {
        $requestData = [
            'call'  => [$path, $data],
            'async' => $async
        ];

        if (null !== $this->key) {
            $requestData = j64_($requestData, $this->key);
        }

        $currentServer = remote(app()->getEnv());

        $client = new \GuzzleHttp\Client(['verify' => $currentServer->ssl]);

        $response = $client->request('POST', $this->handlerUrl, [
            'form_params' => [
                'data' => $requestData
            ],
            'cookies'     => $this->getCookies()
        ]);

        $responseBodyContents = $response->getBody()->getContents();

        $responseOutputs = _j64($responseBodyContents, $this->key);

        if (!empty($responseOutputs['errors'])) {
            $this->controller->log('errors: ' . implode('; ', $responseOutputs['errors']));
        } else {
            return $responseOutputs['content'];
        }
    }

    private function getCookies()
    {
        $cookiePrefix = app()->getConfig('cookies_prefix');
        $tokenCookieName = $cookiePrefix . 't';

        $cookies = [
            $tokenCookieName => 'b725529138d5e271d881d331c24c81f8' // todo
        ];

        $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray($cookies, $this->host);

        return $cookieJar;
    }

    private $authToken;

    public function login($login, $password)
    {

    }
}
