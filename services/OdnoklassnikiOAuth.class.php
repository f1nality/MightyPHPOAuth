<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class OdnoklassnikiOAuth extends OAuth {
    protected $sAuthorizeUrl = 'http://www.odnoklassniki.ru/oauth/authorize';
    protected $sAccessTokenUrl = 'http://api.odnoklassniki.ru/oauth/token.do';
    protected $sAccessTokenOptionalParameters = array('grant_type' => 'authorization_code');
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $sMethod = 'users.getCurrentUser';
        $sSignature = md5('application_key=' . $this->getClientOptional() . 'method=' . $sMethod . md5($this->getAccessToken() . $this->getClientSecret()));

        $aParameters = array(
            'access_token' =>  $this->getAccessToken(),
            'application_key' => $this->getClientOptional(),
            'method' => $sMethod,
            'sig' => $sSignature
        );

        $aResult = $this->makeRequest('http://api.odnoklassniki.ru/fb.do', 'GET', $aParameters);

        if (!$aResult || !isset($aResult['uid'])) {
            return false;
        }

        return 'http://www.odnoklassniki.ru/profile/' . $aResult['uid'];
    }
}