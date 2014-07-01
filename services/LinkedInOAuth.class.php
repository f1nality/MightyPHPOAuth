<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class LinkedInOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://www.linkedin.com/uas/oauth2/authorization';
    protected $sAuthorizeOptionalParameters = array('state' => 'geovS9nRzRostYx2eSHe');
    protected $sAccessTokenUrl = 'https://www.linkedin.com/uas/oauth2/accessToken';
    protected $sAccessTokenOptionalParameters = array('grant_type' => 'authorization_code');
    protected $iOAuthVersion = 2;

    public function getUserLink() {
        $aParameters = array(
            'format' => 'json',
            'oauth2_access_token' => $this->getAccessToken()
        );

        $aResult = $this->makeRequest('https://api.linkedin.com/v1/people/~:(public-profile-url)', 'GET', $aParameters);

        if (!$aResult || !isset($aResult['publicProfileUrl'])) {
            return false;
        }

        return $aResult['publicProfileUrl'];
    }
}