<?php

abstract class OAuth {
    protected $sAuthorizeUrl;
    protected $sAuthorizeOptionalParameters = array();
    protected $sAccessTokenUrl;
    protected $sAccessTokenPost = true;
    protected $sAccessTokenReturnType = 'json';
    protected $sAccessTokenOptionalParameters = array();
    protected $sRequestTokenUrl;
    protected $sUserAgent = null;
    protected $sRedirectUrl;
    protected $iOAuthVersion;
    protected $sScope = '';

    private $sClientId;
    private $sClientSecret;
    private $sClientOptional;
    private $sAccessToken;
    private $sAccessTokenExpiresIn;

    public function __construct($sClientId, $sClientSecret, $sRedirectUrl, $sClientOptional = null) {
        $this->sClientId = $sClientId;
        $this->sClientSecret = $sClientSecret;
        $this->sRedirectUrl = $sRedirectUrl;

        if ($sClientOptional) {
            $this->sClientOptional = $sClientOptional;
        }
    }

    public function getClientId() {
        return $this->sClientId;
    }

    public function getClientSecret() {
        return $this->sClientSecret;
    }

    public function getClientOptional() {
        return $this->sClientOptional;
    }

    public function getAccessToken() {
        return $this->sAccessToken;
    }

    public function getAccessTokenExpiresIn() {
        return $this->sAccessTokenExpiresIn;
    }

    public function getAccessTokenSecret() {
        return $_SESSION['oauth_token_secret'];
    }

    public function getAuthorizationPageUrl() {
        if ($this->iOAuthVersion == 1) {
            $aRequestTokenResult = $this->getRequestToken();
            $_SESSION['oauth_token_secret'] = $aRequestTokenResult['oauth_token_secret'];

            $aParameters = array(
                'oauth_token' => $aRequestTokenResult['oauth_token']
            );
        } else if ($this->iOAuthVersion == 2) {
            $aParameters = array(
                'client_id' => $this->sClientId,
                'scope' => $this->sScope,
                'redirect_uri' => $this->sRedirectUrl,
                'response_type' => 'code'
            );
        } else {
            throw new Exception('Unsupported OAuth version');
        }

        $aParameters = array_merge($aParameters, $this->sAuthorizeOptionalParameters);

        return $this->sAuthorizeUrl . '?' . http_build_query($aParameters);
    }

    private function getAccessTokenMethod() {
        return $this->sAccessTokenPost ? 'POST' : 'GET';
    }

    protected function makeAccessTokenRequest($aRedirectUrlResponseParameters) {
        if ($this->iOAuthVersion == 1) {
            $aParameters = array();

            $aAuthorizationHeader = array_merge($this->getOAuth1Parameters(), array(
                'oauth_token' => $aRedirectUrlResponseParameters['oauth_token'],
                'oauth_verifier' => $aRedirectUrlResponseParameters['oauth_verifier']
            ));

            $sSignature = $this->buildSignature($this->sAccessTokenUrl, $this->getAccessTokenMethod(), $aAuthorizationHeader, $this->sClientSecret, $_SESSION['oauth_token_secret']);
            $sHeader = $this->buildAuthorizationHeader(array_merge($aAuthorizationHeader, array(
                'oauth_signature' => $sSignature
            )));

            $aHeaders = array($sHeader);
        } else if ($this->iOAuthVersion == 2) {
            $aParameters = array(
                'code' => $aRedirectUrlResponseParameters['code'],
                'client_id' => $this->sClientId,
                'client_secret' => $this->sClientSecret,
                'redirect_uri' => $this->sRedirectUrl
            );

            $aHeaders = array();
        } else {
            throw new Exception('Unsupported OAuth version');
        }

        $aParameters = array_merge($aParameters, $this->sAccessTokenOptionalParameters);

        return $this->makeRequest($this->sAccessTokenUrl, $this->getAccessTokenMethod(), $aParameters, $aHeaders, $this->sAccessTokenReturnType);
    }

    public function validateAccessToken($aRedirectUrlResponseParameters) {
        if ($this->sAccessToken) {
            return true;
        }

        $aAccessTokenResult = $this->makeAccessTokenRequest($aRedirectUrlResponseParameters);

        if (!$aAccessTokenResult) {
            return false;
        }

        if ($this->iOAuthVersion == 1 && isset($aAccessTokenResult['oauth_token']) && isset($aAccessTokenResult['oauth_token_secret'])) {
            $this->sAccessToken = $aAccessTokenResult['oauth_token'];
            $_SESSION['oauth_token_secret'] = $aAccessTokenResult['oauth_token_secret'];
        } else if ($this->iOAuthVersion == 2 && isset($aAccessTokenResult['access_token'])) {
            $this->sAccessToken = $aAccessTokenResult['access_token'];
            $this->sAccessTokenExpiresIn = $aAccessTokenResult['expires_in'];
        } else {
            return false;
        }

        return true;
    }

    protected function getOAuth1Parameters() {
        return array(
            'oauth_consumer_key' => $this->sClientId,
            'oauth_nonce' => md5(time() . rand(1000000, 9999999)),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );
    }

    protected function getRequestToken() {
        $aParameters = array_merge($this->getOAuth1Parameters(), array(
            'oauth_callback' => $this->sRedirectUrl
        ));

        $sSignature = $this->buildSignature($this->sRequestTokenUrl, 'POST', $aParameters, $this->sClientSecret);
        $sHeader = $this->buildAuthorizationHeader(array_merge($aParameters, array(
            'oauth_signature' => $sSignature
        )));

        $aHeaders = array($sHeader);

        return $this->makeRequest($this->sRequestTokenUrl, 'POST', array(), $aHeaders, 'query');
    }

    protected function buildSignature($sUrl, $sMethod, $aParameters, $sClientSecret, $sTokenSecret = '') {
        $sSig = '';

        ksort($aParameters);

        foreach ($aParameters AS $key => $value) {
            if (!empty($sSig)) {
                $sSig .= '&';
            }

            $sSig .= $key;
            $sSig .= '=';
            $sSig .= rawurlencode($value);
        }

        $sSignatureBase = strtoupper($sMethod) . '&' . rawurlencode($sUrl) . '&' . rawurlencode($sSig);
        $sSigningKey = rawurlencode($sClientSecret) . '&' . rawurlencode($sTokenSecret);

        return base64_encode(hash_hmac('sha1', $sSignatureBase, $sSigningKey, true));
    }

    protected function buildAuthorizationHeader($aParameters){
        $result = 'Authorization: OAuth ';
        $values = array();

        foreach($aParameters as $key => $value){
            $values[] = $key . '="' . rawurlencode($value) . '"';
        }

        $result .= implode(', ', $values);

        return $result;
    }


    protected function makeRequest($sUrl, $sMethod = 'GET', $aParameters = array(), $aHeaders = array(), $sReturnType = 'json') {
        $sParameters = http_build_query($aParameters);

        $oCurl = curl_init();

        if ($sMethod == 'POST') {
            curl_setopt($oCurl, CURLOPT_POST, true);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sParameters);
        } else {
            $sUrl .= '?' . $sParameters;
        }

        curl_setopt($oCurl, CURLOPT_URL, $sUrl);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aHeaders);
        curl_setopt($oCurl, CURLOPT_ENCODING, '');

        if ($this->sUserAgent) {
            curl_setopt($oCurl, CURLOPT_USERAGENT, $this->sUserAgent);
        }

        $sOutput = curl_exec($oCurl);

        curl_close($oCurl);

        if (!$sOutput) {
            return false;
        }

        if ($sReturnType == 'json') {
            return json_decode($sOutput, true);
        } else if ($sReturnType == 'query') {
            parse_str($sOutput, $aOutput);

            return $aOutput;
        } else {
            return $sOutput;
        }
    }
}