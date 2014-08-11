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
$settings->client_id = "0000000040127B4F";
$settings->redirect_uri = "http://www.adresboek.email/api/live.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->Live($settings);