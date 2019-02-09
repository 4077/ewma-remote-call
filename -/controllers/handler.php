<?php namespace ewma\remoteCall\controllers;

class Handler extends \Controller
{
    public function handle()
    {
        $requestSvc = $this->app->request;
        $requestData = $requestSvc->request->get('data') ?: $requestSvc->query->get('data');

        $currentServer = remote($this->_env());

        $responseErrors = [];

        if (empty($requestData)) {
            $responseErrors[] = 'empty request data';
        } else {
            if ($currentServer) {
                $enabled = true; // hardcode

                if ($enabled) {
                    if ($key = $currentServer->getKey()) {
                        $requestData = _j64($requestData, $key);

                        if (!$requestData) {
                            $responseErrors[] = 'wrong key for env=' . $this->_env();
                        }
                    } else {
                        $responseErrors[] = 'not isset key for server with env=' . $this->_env();
                    }
                } else {
                    $responseErrors[] = 'server with env=' . $this->_env() . ' disabled';
                }
            } else {
                $responseErrors[] = 'server for env=' . $this->_env() . ' not configured';
            }
        }

        $responseContent = null;

        if (empty($responseErrors)) {
            if ($callData = $requestData['call'] ?? false) {
                $call = appc()->_call($callData);

                $async = $requestData['async'] ?? false;

                if ($async) {
                    $responseContent = $call->async();
                } else {
                    $responseContent = $call->perform();
                }
            }

            $this->log('input: ' . j_($requestData));
            $this->log('output: ' . j_($responseContent));
        } else {
            $this->log('errors: ' . implode('; ', $responseErrors));
        }

        $responseData = [
            'errors'  => $responseErrors,
            'content' => $responseContent
        ];

        return j64_($responseData, $currentServer->getKey());
    }
}
