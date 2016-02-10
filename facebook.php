<?php
/**
 * Description of facebook
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
/*
require_once(getcwd()."/Curl.php");
require_once(getcwd()."/Redirect.php");

$_SESSION["facebook_client_id"] = "642821282413842";
$_SESSION["facebook_secret"] = "bbd7fc52436f1b5d9a754c71b43f583d";
$_SESSION["facebook_redirect"] = urlencode("http://www.adresboek.email/api/facebook.auth.php");
$csrf = md5(rand(0, 1000));

$url = "https://graph.facebook.com/oauth/authorize?client_id={$_SESSION["facebook_client_id"]}&"
. "redirect_uri={$_SESSION["facebook_redirect"]}";
Redirect::To($url);
*/
require_once(getcwd()."/Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = 1704229076527706;
$settings->client_secret = "432848fa117bb630351bf72939bda69e";
$settings->redirect_uri = "https://www.thecodingcompany.se/oauth2/facebook.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->Facebook($settings);