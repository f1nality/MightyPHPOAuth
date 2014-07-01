<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class HeadHunterOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://m.hh.ru/oauth/authorize';
    protected $sAccessTokenUrl = 'https://m.hh.ru/oauth/token';
    protected $sAccessTokenOptionalParameters = array('grant_type' => 'authorization_code');
    protected $sUserAgent = 'CURL';
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'access_token' => $this->getAccessToken()
        );

        $aHeaders = array('Authorization: Bearer ' . $this->getAccessToken());

        $aResult = $this->makeRequest('https://api.hh.ru/resumes/mine', 'GET', $aParameters, $aHeaders);

        if (!$aResult || !isset($aResult['items'][0]['alternate_url'])) {
            return false;
        }

        return $aResult['items'][0]['alternate_url'];
    }
}