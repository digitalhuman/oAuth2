<?php
require_once("Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = "";
$settings->client_secret = "";
$settings->redirect_uri = "https://www.thecodingcompany.se/oauth2/fitbit.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->FitBit($settings);