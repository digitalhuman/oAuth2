<?php
/**
 * Description of gmail
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 * https://console.developers.google.com/project
 */

require_once(getcwd()."/Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = "";
$settings->client_secret = "";
$settings->redirect_uri = "https://www.thecodingcompany.se/oauth2/gmail.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->GMail($settings);