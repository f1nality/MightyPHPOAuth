<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class StackoverflowOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://stackexchange.com/oauth';
    protected $sAccessTokenUrl = 'https://stackexchange.com/oauth/access_token';
    protected $sAccessTokenReturnType = 'query';
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'site' => 'stackoverflow',
            'access_token' => $this->getAccessToken(),
            'key' => $this->getClientOptional()
        );

        $aResult = $this->makeRequest('https://api.stackexchange.com/2.2/me', 'GET', $aParameters);

        if (!$aResult || !isset($aResult['items'][0]['link'])) {
            return false;
        }

        return $aResult['items'][0]['link'];
    }
}