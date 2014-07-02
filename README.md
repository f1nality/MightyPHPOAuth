MightyPHPOAuth
==============

PHP OAuth library

Example
========================

Object initialization

```php
$OAuth = new FacebookOAuth('CLIENT_ID', 'CLIENT_SECRET', 'http://localhost/oauth_callback');
```

oauth.tpl
```html
<script>
window.open({$OAuth->getAuthorizationPageUrl()})
</script>
```


oauth_callback.php
```php
if ($OAuth->validateAccessToken($_GET)) {
    echo 'My profile url:' . $OAuth->getUserLink();
}
```
