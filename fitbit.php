<?php
require_once("Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = "227FQB";
$settings->client_secret = "755a6e5cea76c05d4b27a319d942a10e";
$settings->redirect_uri = "https://www.thecodingcompany.se/fitbit.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->FitBit($settings);