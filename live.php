<?php
/**
 * Description of live
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
require_once(getcwd()."/Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = "";
$settings->redirect_uri = "https://www.thecodingcompany.se/oauth2/live.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->Live($settings);