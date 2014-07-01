MightyPHPOAuth
==============

PHP OAuth library

Example:

$OAuth = new FacebookOAuth('CLIENT_ID', 'CLIENT_SECRET', 'http://localhost/oauth_callback');

oauth.tpl
<script>
window.open({$OAuth->getAuthorizationPageUrl()})
</script>


oauth_callback.php
if ($OAuth->validateAccessToken($_GET)) {
	echo 'My profile url:' . $OAuth->getUserLink();
}
