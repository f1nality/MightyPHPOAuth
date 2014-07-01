<?php

require_once(dirname(__FILE__) . '/services/OAuth.class.php');

class TwitterOAuth extends OAuth {
    protected $sAuthorizeUrl = 'https://api.twitter.com/oauth/authorize';
    protected $sAccessTokenUrl = 'https://api.twitter.com/oauth/access_token';
    protected $sAccessTokenReturnType = 'query';
    protected $sRequestTokenUrl = 'https://api.twitter.com/oauth/request_token';
    protected $iOAuthVersion = 1;
    protected $sScreenName;

    protected function makeAccessTokenRequest($aRedirectUrlResponseParameters) {
        $aAccessTokenResult = parent::makeAccessTokenRequest($aRedirectUrlResponseParameters);

        if (isset($aAccessTokenResult['screen_name'])) {
            $this->sScreenName = $aAccessTokenResult['screen_name'];
        }

        return $aAccessTokenResult;
    }

    public function getUserLink() {
        return 'https://twitter.com/' . $this->sScreenName;
    }
}