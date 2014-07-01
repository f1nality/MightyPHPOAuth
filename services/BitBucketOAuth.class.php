<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class BitBucketOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://bitbucket.org/api/1.0/oauth/authenticate';
    protected $sAccessTokenUrl = 'https://bitbucket.org/api/1.0/oauth/access_token';
    protected $sAccessTokenReturnType = 'query';
    protected $sRequestTokenUrl = 'https://bitbucket.org/api/1.0/oauth/request_token';
    protected $iOAuthVersion = 1;

    public function getUserLink() {
        $sUrl = 'https://bitbucket.org/api/1.0/user';
        $aParameters = array_merge($this->getOAuth1Parameters(), array(
            'oauth_token' => $this->getAccessToken()
        ));

        $sSignature = $this->buildSignature($sUrl, 'GET', $aParameters, $this->getClientSecret(), $this->getAccessTokenSecret());
        $sHeader = $this->buildAuthorizationHeader(array_merge($aParameters, array(
            'oauth_signature' => $sSignature
        )));

        $aResult = $this->makeRequest($sUrl, 'GET', array(), array($sHeader));

        if (!$aResult || !isset($aResult['user']['username'])) {
            return false;
        }

        return 'https://bitbucket.org/' . $aResult['user']['username'];
    }
}