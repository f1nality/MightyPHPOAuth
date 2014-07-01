<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class VKOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://oauth.vk.com/authorize';
    protected $sAuthorizeOptionalParameters = array('v' => '5.21');
    protected $sAccessTokenUrl = 'https://oauth.vk.com/access_token';
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'v' => '5.21',
            'access_token' => $this->getAccessToken()
        );

        $aResult = $this->makeRequest('https://api.vk.com/method/users.get', 'GET', $aParameters);

        if (!$aResult || !isset($aResult['response'][0]['id'])) {
            return false;
        }

        return 'http://vk.com/id' . $aResult['response'][0]['id'];
    }
}