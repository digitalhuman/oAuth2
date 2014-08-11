<?php

/**
 * Description of Redirect
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

class Redirect {
    
    private function __construct() {
    }
    
    public static function To($url = ""){
        header("Location: {$url}", true);
        die();
    }
}
