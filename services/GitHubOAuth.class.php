<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class GitHubOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://github.com/login/oauth/authorize';
    protected $sAccessTokenUrl = 'https://github.com/login/oauth/access_token';
    protected $sAccessTokenReturnType = 'query';
    protected $sUserAgent = 'CURL';
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'access_token' => $this->getAccessToken()
        );

        $aResult = $this->makeRequest('https://api.github.com/user', 'GET', $aParameters);

        if (!$aResult || !isset($aResult['login'])) {
            return false;
        }

        return 'https://github.com/' . $aResult['login'];

    }
}