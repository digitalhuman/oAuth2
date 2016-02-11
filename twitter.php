<pre>
<?php
require_once("Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = "";
$settings->client_secret = "";
$settings->access_token = "";
$settings->access_token_secret = "";
$settings->redirect_uri = "https://www.thecodingcompany.se/oauth2/twitter.php";

ini_set('display_errors', 1);


$oauth = new oAuth2Validate();
$oauth->Twitter($settings);
