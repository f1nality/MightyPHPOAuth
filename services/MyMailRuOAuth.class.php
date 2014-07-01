<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class MyMailRuOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://connect.mail.ru/oauth/authorize';
    protected $sAccessTokenUrl = 'https://connect.mail.ru/oauth/token';
    protected $sAccessTokenOptionalParameters = array('grant_type' => 'authorization_code');
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $sMethod = 'users.getInfo';
        $sSignature = md5('client_id=' . $this->getClientId() . 'method=' . $sMethod . 'secure=1session_key=' . $this->getAccessToken() . $this->getClientSecret());

        $aParameters = array(
            'secure' => 1,
            'session_key' =>  $this->getAccessToken(),
            'client_id' => $this->getClientId(),
            'method' => $sMethod,
            'sig' => $sSignature
        );

        $aResult = $this->makeRequest('http://www.appsmail.ru/platform/api', 'GET', $aParameters);

        if (!$aResult || !isset($aResult[0]['link'])) {
            return false;
        }

        return $aResult[0]['link'];
    }
}