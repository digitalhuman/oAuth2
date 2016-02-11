# oAuth2
oAuth2 class to validate users through different socialmedia platforms using oAuth2


*Usage example (Twitter)*

```php
require_once("Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = ""; //Consumer key or Client ID
$settings->client_secret = ""; //Consumer secret or Client secret
$settings->access_token = ""; //For Twitter
$settings->access_token_secret = ""; //Not in use at this time of writing
$settings->redirect_uri = "https://www.thecodingcompany.se/oauth2/twitter.php";

ini_set('display_errors', 1);

$oauth = new oAuth2Validate();
$oauth->Twitter($settings);
```

Set callback URL in the appropriate platform to: http://yoururl/twitter.php
