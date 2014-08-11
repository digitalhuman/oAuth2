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
$settings->client_id = "913363285785-hhto2qr3npmkqqpc2svigpkt90ig7vvh.apps.googleusercontent.com";
$settings->client_secret = "M4KiyOkj_DJ5jejZep8oi1S0";
$settings->redirect_uri = "http://www.adresboek.email/api/gmail.php";

ini_set('display_errors', 1);
$oauth = new oAuth2Validate();
$oauth->GMail($settings);