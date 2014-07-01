<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class FacebookOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://graph.facebook.com/oauth/authorize';
    protected $sAccessTokenUrl = 'https://graph.facebook.com/oauth/access_token';
    protected $sAccessTokenReturnType = 'query';
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'access_token' => $this->getAccessToken()
        );

        $aResult = $this->makeRequest('https://graph.facebook.com/me', 'GET', $aParameters);

        if (!$aResult || !isset($aResult['link'])) {
            return false;
        }

        return $aResult['link'];
    }
}