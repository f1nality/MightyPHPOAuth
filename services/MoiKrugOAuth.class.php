<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class MoiKrugOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://oauth.yandex.ru/authorize';
    protected $sAccessTokenUrl = 'https://oauth.yandex.ru/token';
    protected $sAccessTokenOptionalParameters = array('grant_type' => 'authorization_code');
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'oauth_token' => $this->getAccessToken()
        );

        $aResult = $this->makeRequest('http://api.moikrug.ru/v1/my/', 'GET', $aParameters);

        if (!$aResult || !isset($aResult[0]['link'])) {
            return false;
        }

        return $aResult[0]['link'];
    }
}