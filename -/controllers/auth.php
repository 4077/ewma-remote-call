<?php namespace ewma\remoteCall\controllers;

class Auth extends \Controller
{
    public function getUserToken()
    {
        $login = $this->data('login');
        $pass = $this->data('pass');

        $user = \ewma\access\models\User::where('login', $login)->first();

        if ($user) {
            $verified = password_verify($pass, $user->pass);

            if ($verified) {
                return $user->token;
            }
        }
    }
}
