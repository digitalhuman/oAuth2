<?php
/**
 * Description of Curl
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */
session_start();
ini_set('display_errors', 0);

class Curl {
    
    private static $curl = null;
    
    private function __construct() {
    }
    
    public static function Get($url = ""){
        static::$curl = curl_init($url);
        curl_setopt(static::$curl, CURLOPT_HEADER, 0);
            /*
             * HTTP Basic Auth         
            curl_setopt(self::curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt(self::curl, CURLOPT_USERPWD, $credentials["username"].":".$credentials["password"]);
            * 
            */
            curl_setopt(static::$curl, CURLOPT_TIMEOUT, 30);
            curl_setopt(static::$curl, CURLOPT_POST, 0);
            curl_setopt(static::$curl, CURLOPT_COOKIESESSION, TRUE);
            curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt(static::$curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt(static::$curl, CURLOPT_SSL_VERIFYHOST, FALSE);            
            $res = curl_exec(static::$curl);
            if(($json = @json_decode($res, true)) == true){
                return $json;
            }elseif(($xml = @simplexml_load_string($res)) == true){
                return static::result_array($xml);
            }else{
                return $res;
            }
    }
    private static function result_array($q){
        return json_decode(json_encode((array)$q), true);
    }
    
    /**
     * GET using Authorization header
     * @param type $url
     * @param type $code
     * @param type $parameters
     * @return boolean
     */
    public static function AuthorizationGET($url = "", $header_parameters = array(), $post_parameters = array()){
        
        $header = "";
        if(is_array($header_parameters)){
            foreach($header_parameters as $k => $v){
                $header .= "{$k}=\"{$v}\"\r\n";
            }
        }else{
            $header = $header_parameters;
        }
        
        $opts = array(
            'http'=>array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                            "Authorization: {$header}\r\n",
                'content' => ''
            )
        );
        
        //Create post content
        if(count($post_parameters) > 0){
            $content = "";                
            foreach($post_parameters as $k => $v){
                $content .= "&{$k}=".$v;
            }
            $opts["http"]["content"] = substr($content, 1);
        }else{
            unset($opts["http"]["content"]);
        }
        
        $context = stream_context_create($opts);
        
        $fp = fopen($url, 'r', false, $context);
        if(($data = fgets($fp)) !== false){
            return json_decode($data, true);
        }else{
            return false;
        }
    }
    
    /**
     * POST using Authorization header
     * @param type $url
     * @param type $code
     * @param type $parameters
     * @return boolean
     */
    public static function AuthorizationPOST($url = "", $header_parameters = array(), $post_parameters = array()){
        
        $header = "";
        if(is_array($header_parameters)){
            foreach($header_parameters as $k => $v){
                $header .= "{$k}=\"{$v}\"\r\n";
            }
        }else{
            $header = $header_parameters;
        }
        
        $opts = array(
            'http'=>array(
                'method' => "POST",
                'header' => "Accept-language: en\r\n" .
                            "Authorization: {$header}\r\n",
                'content' => ''
            )
        );
                            
        //Create post content
        if(count($post_parameters) > 0){
            $content = "";                
            foreach($post_parameters as $k => $v){
                $content .= "&{$k}=".$v;
            }
            $opts["http"]["content"] = substr($content, 1);
        }else{
            unset($opts["http"]["content"]);
        }
        
        $context = stream_context_create($opts);
        
        $fp = fopen($url, 'r', false, $context);
        if(($data = fgets($fp)) !== false){
            return json_decode($data, true);
        }else{
            return false;
        }
    }
    
    /**
     * Post using Authorization header
     * @param type $url
     * @param type $code
     * @param type $parameters
     * @return boolean
     */
    public static function AuthorizedPost($url = "", $credentials = "", $parameters = array()){
        $opts = array(
            'http'=>array(
                'method' => "POST",
                'header' => "Accept-language: en\r\n" .
                            "Authorization: Basic {$credentials}\r\n" .
                            "Content-type: application/x-www-form-urlencoded;charset=UTF-8\r\n",
                'content' => ''
            )
        );
        $content = "";                
        foreach($parameters as $k => $v){
            $content .= "&{$k}=".$v;
        }
        //Strip first & sign
        $opts["http"]["content"] = substr($content, 1);
        
        $context = stream_context_create($opts);
        
        $fp = fopen($url, 'r', false, $context);
        if(($data = fgets($fp)) !== false){
            return json_decode($data, true);
        }else{
            return false;
        }
    }
    
    /**
     * Fitbit GET
     * @param type $url
     * @param type $credentials
     * @param type $parameters
     * @return boolean
     */
    public static function AuthorizedGet($url = "", $credentials = "", $parameters = array()){
        $opts = array(
            'http'=>array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                            "Authorization: Bearer {$credentials}\r\n" .
                            "Content-type: application/x-www-form-urlencoded;charset=UTF-8\r\n",
                'content' => ''
            )
        );
        $content = "";                
        foreach($parameters as $k => $v){
            $content .= "&{$k}=".$v;
        }
        //Strip first & sign
        $opts["http"]["content"] = substr($content, 1);
        
        $context = stream_context_create($opts);
        
        $fp = fopen($url, 'r', false, $context);
        if(($data = fgets($fp)) !== false){
            return json_decode($data, true);
        }else{
            return false;
        }
    }
    
    /**
     * Get Token for Twitter
     * @param type $url
     * @param type $parameters
     * @param type $credentials
     * @return boolean
     */
    public static function TwitterPost($url = "", $parameters = "", $credentials = ""){
        $opts = array(
            'http'=>array(
                'method' => "POST",
                'header' => "Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n" .
                            "Authorization: Basic {$credentials}\r\n" .
                            "Content-type: application/x-www-form-urlencoded;charset=UTF-8\r\n",
                'content' => 'grant_type=client_credentials'
            )
        );

        $context = stream_context_create($opts);
        
        $fp = fopen($url, 'r', false, $context);
        if(($data = fgets($fp)) !== false){
            return json_decode($data, true);
        }else{
            return false;
        }
    }
    
    /**
     * oAuth2 Twitter POST method
     * @param type $url
     * @param type $parameters
     * @return type
     */
    public static function oAuthPost($url = "", $parameters = ""){
        static::$curl = curl_init();
        curl_setopt(static::$curl, CURLOPT_URL, $url);
        curl_setopt(static::$curl, CURLOPT_HEADER, 1);
        /*
         * HTTP Basic Auth         
        curl_setopt(self::curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt(self::curl, CURLOPT_USERPWD, $credentials["username"].":".$credentials["password"]);
        * 
        */       
        curl_setopt(static::$curl, CURLOPT_TIMEOUT, 30);
        curl_setopt(static::$curl, CURLOPT_POST, 1);
        curl_setopt(static::$curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt(static::$curl, CURLOPT_HTTPHEADER, array(
            "User-Agent: thecodingcompany.se HTTP Client",
            "Authorization: " . $parameters
        ));
        curl_setopt(static::$curl, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt(static::$curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt(static::$curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt(static::$curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt(static::$curl, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt(static::$curl, CURLOPT_VERBOSE, TRUE);
        $res = curl_exec(static::$curl);
        $info = curl_getinfo(static::$curl);
        print_r($info['request_header']);
        echo "<p><br/></p>";
        print_r($res);
        die();
        
        if(($json = @json_decode($res, true)) == true){
            return $json;
        }elseif(($xml = @simplexml_load_string($res)) == true){
            return static::result_array($xml);
        }else{
            return $res;
        }
    }
    
    public static function Post($url = "", $parameters = ""){
        static::$curl = curl_init($url);
        curl_setopt(static::$curl, CURLOPT_HEADER, 0);
        /*
         * HTTP Basic Auth         
        curl_setopt(self::curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt(self::curl, CURLOPT_USERPWD, $credentials["username"].":".$credentials["password"]);
        * 
        */
        curl_setopt(static::$curl, CURLOPT_TIMEOUT, 30);
        curl_setopt(static::$curl, CURLOPT_POST, 1);
        curl_setopt(static::$curl, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt(static::$curl, CURLOPT_COOKIESESSION, TRUE);
        curl_setopt(static::$curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt(static::$curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt(static::$curl, CURLOPT_SSL_VERIFYHOST, FALSE);            
        $res = curl_exec(static::$curl);
        if(($json = json_decode($res, true)) == true){
            return $json;
        }elseif(($xml = simplexml_load_string($res)) == true){
            return static::result_array($xml);
        }else{
            return $res;
        }
    }
}