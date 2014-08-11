<?php
/**
 * Description of linkedin
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
require_once(getcwd()."/Auth2Class.php");

$settings = new oAuth2Settings();
$settings->client_id = "7717xoqi9o9i0g";
$settings->client_secret = "2RkiahA9YRx8vOjY";
$settings->redirect_uri = "http://www.adresboek.email/api/linkedin.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->LinkedIN($settings);