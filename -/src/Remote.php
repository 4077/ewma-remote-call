<?php namespace ewma\remoteCall;

class Remote
{
    public static $instances = [];

    /**
     * @return \ewma\remoteCall\Remote
     */
    public static function getInstance($connectionString)
    {
        if (!isset(static::$instances[$connectionString])) {
            static::$instances[$connectionString] = new self($connectionString);
        }

        return static::$instances[$connectionString];
    }

    private $controller;

    private $env;

    private $ssl;

    private $key;

    private $host;

    private $handlerUrl;

    private $configured;

    public function __construct($connectionString)
    {
        // todo [app:]env

        $this->env = \ewma\apps\models\Env::where('app_id', 0)->where('name', $connectionString)->first();

        if ($this->env) {
            $connection = \ewma\remoteCall\models\Connection::where('env_id', $this->env->id)->first();

            if ($connection) {
                $this->ssl = $connection->ssl;
                $this->host = $connection->host;
                $this->key = $connection->key;

                $scheme = $this->ssl ? 'https' : 'http';

                $this->handlerUrl = $scheme . '://' . path($this->host, 'remote-call') . '/';

                $this->configured = true;
            }
        }

        $this->controller = appc('\ewma\remoteCall -');
    }

    public function getKey()
    {
        return $this->key;
    }

    public function isConfigured()
    {
        return $this->configured;
    }

    public function isCurrent()
    {
        return $this->env->name === app()->getEnv();
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
