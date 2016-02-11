<?php
/* Generic exception class
 */
class OAuthException extends Exception{}

class OAuthConsumer {

    public $key;
    public $secret;

    function __construct($key, $secret, $callback_url = NULL) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callback_url = $callback_url;
    }

    function __toString() {
        return "OAuthConsumer[key=$this->key,secret=$this->secret]";
    }

}

class OAuthToken {

    // access tokens and request tokens
    public $key;
    public $secret;

    /**
     * key = the token
     * secret = the token secret
     */
    function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * generates the basic string serialization of a token that a server
     * would respond to request_token and access_token calls with
     */
    function to_string() {
        return "oauth_token=" .
            OAuthUtil::urlencode_rfc3986($this->key) .
            "&oauth_token_secret=" .
            OAuthUtil::urlencode_rfc3986($this->secret);
    }

    function __toString() {
        return $this->to_string();
    }

}

/**
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class OAuthSignatureMethod {

    /**
     * Needs to return the name of the Signature Method (ie HMAC-SHA1)
     * @return string
     */
    abstract public function get_name();

    /**
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in OAuthRequest when the final
     * request is serialized
     * @param OAuthRequest $request
     * @param OAuthConsumer $consumer
     * @param OAuthToken $token
     * @return string
     */
    abstract public function build_signature($request, $consumer, $token);

    /**
     * Verifies that a given signature is correct
     * @param OAuthRequest $request
     * @param OAuthConsumer $consumer
     * @param OAuthToken $token
     * @param string $signature
     * @return bool
     */
    public function check_signature($request, $consumer, $token, $signature) {
        $built = $this->build_signature($request, $consumer, $token);

        // Check for zero length, although unlikely here
        if (strlen($built) == 0 || strlen($signature) == 0) {
            return false;
        }

        if (strlen($built) != strlen($signature)) {
            return false;
        }

        // Avoid a timing leak with a (hopefully) time insensitive compare
        $result = 0;
        for ($i = 0; $i < strlen($signature); $i++) {
            $result |= ord($built{$i}) ^ ord($signature{$i});
        }

        return $result == 0;
    }

}

/**
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104] 
 * where the Signature Base String is the text and the key is the concatenated values (each first 
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&' 
 * character (ASCII code 38) even if empty.
 *   - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {

    function get_name(){
        return "HMAC-SHA1";
    }

    public function build_signature($request, $consumer, $token) {
        $base_string = $request->get_signature_base_string();
        $request->base_string = $base_string;
        
        $key_parts = array(
          $consumer->secret,
          ($token) ? $token->secret : ""
        );

        $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);
        
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }

}

class OAuthRequest {

    protected $parameters;
    protected $http_method;
    protected $http_url;
    // for debug purposes
    public $base_string;
    public static $version = '1.0';
    public static $POST_INPUT = 'php://input';

    /**
     * Constructor
     * @param type $http_method
     * @param type $http_url
     * @param type $parameters
     */
    function __construct($http_method, $http_url, $parameters = NULL) {
        $parameters         = ($parameters) ? $parameters : array();
        $parameters         = array_merge(OAuthUtil::parse_parameters(parse_url($http_url, PHP_URL_QUERY)), $parameters);
        $this->parameters   = $parameters;
        $this->http_method  = $http_method;
        $this->http_url     = $http_url;
    }    

    /**
     * Helper method to create the important part of the request
     * @param type $consumer Consumer object
     * @param type $token   Null or Token object
     * @param type $http_method GET/POST
     * @param type $http_url    URL
     * @param type $parameters  Parameters to send
     * @return \OAuthRequest
     */
    public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters = NULL) {
        $parameters = ($parameters) ? $parameters : array();
        $defaults = array(
            "oauth_version"         => OAuthRequest::$version,
            "oauth_nonce"           => OAuthRequest::generate_nonce(),
            "oauth_timestamp"       => OAuthRequest::generate_timestamp(),
            "oauth_consumer_key"    => $consumer->key
        );
        if($token){
            $defaults['oauth_token'] = $token->key;
        }
        
        $parameters = array_merge($defaults, $parameters);
        return new OAuthRequest($http_method, $http_url, $parameters);
    }

    /**
     * Set request parameter
     * @param type $name
     * @param type $value
     * @param type $allow_duplicates
     */
    public function set_parameter($name, $value, $allow_duplicates = true) {
        if ($allow_duplicates && isset($this->parameters[$name])) {
            // We have already added parameter(s) with this name, so add to the list
            if (is_scalar($this->parameters[$name])) {
                // This is the first duplicate, so transform scalar (string)
                // into an array so we can add the duplicates
                $this->parameters[$name] = array($this->parameters[$name]);
            }

            $this->parameters[$name][] = $value;
        }
        else {
            $this->parameters[$name] = $value;
        }
    }
    public function get_parameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
    public function get_parameters() {
        return $this->parameters;
    }
    public function unset_parameter($name) {
        unset($this->parameters[$name]);
    }

    /**
     * The request parameters, sorted and concatenated into a normalized string.
     * @return string
     */
    public function get_signable_parameters(){
        // Grab all parameters
        $params = $this->parameters;

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if(isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }
        return OAuthUtil::build_http_query($params);
    }

    /**
     * Returns the base string of this request
     *
     * The base string defined as the method, the url
     * and the parameters (normalized), each urlencoded
     * and the concated with &.
     */
    public function get_signature_base_string(){
        $parts = array(
            $this->get_normalized_http_method(),
            $this->get_normalized_http_url(),
            $this->get_signable_parameters()
        );
        $parts = OAuthUtil::urlencode_rfc3986($parts);
        return implode('&', $parts);
    }

    /**
     * Just uppercases the HTTP method
     */
    public function get_normalized_http_method() {
        return strtoupper($this->http_method);
    }

    /**
     * Parses the url and rebuilds it to be
     * scheme://host/path
     */
    public function get_normalized_http_url() {
        $parts = parse_url($this->http_url);

        $scheme = (isset($parts['scheme'])) ? $parts['scheme'] : 'http';
        $port = (isset($parts['port'])) ? $parts['port'] : (($scheme == 'https') ? '443' : '80');
        $host = (isset($parts['host'])) ? strtolower($parts['host']) : '';
        $path = (isset($parts['path'])) ? $parts['path'] : '';

        if (($scheme == 'https' && $port != '443') || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        return "$scheme://$host$path";
    }

    /**
     * Builds a URL usable for a GET request
     */
    public function to_url() {
        $post_data = $this->to_postdata();
        $out = $this->get_normalized_http_url();
        if($post_data){
            $out .= "?{$post_data}";
        }
        return $out;
    }

    /**
     * Builds the data one would send in a POST request
     */
    public function to_postdata() {
        return OAuthUtil::build_http_query($this->parameters);
    }

    /**
     * Builds the Authorization: header
     */
    public function to_header() {
        $first = true;
        $out = 'Authorization: OAuth';

        foreach($this->parameters as $k => $v) {
            if(substr($k, 0, 5) != "oauth") {
                continue;
            }
            if(is_array($v)) {
                throw new OAuthException('Arrays not supported in headers');
            }
            $out .= ($first) ? ' ' : ',';
            $out .= OAuthUtil::urlencode_rfc3986($k) .
                '="' .
                OAuthUtil::urlencode_rfc3986($v) .
                '"';
            $first = false;
        }
        return $out;
    }

    public function __toString() {
        return $this->to_url();
    }

    /**
     * Sign request and add signature parameter
     * @param type $signature_method
     * @param type $consumer
     * @param type $token
     */
    public function sign_request($signature_method, $consumer, $token) {
        $this->set_parameter(
            "oauth_signature_method", $signature_method->get_name(), false
        );
        $signature = $this->build_signature($signature_method, $consumer, $token);

        $this->set_parameter("oauth_signature", $signature, false);
    }

    /**
     * Build the string and sign the data
     * @param type $signature_method
     * @param type $consumer
     * @param type $token
     * @return type
     */
    public function build_signature($signature_method, $consumer, $token) {
        $signature = $signature_method->build_signature($this, $consumer, $token);
        return $signature;
    }

    /**
     * Util function: current timestamp
     */
    private static function generate_timestamp() {
        return time();
    }

    /**
     * Util function: current nonce
     */
    private static function generate_nonce() {
        $mt = microtime();
        $rand = mt_rand();
        return md5($mt . $rand); // md5s look nicer than numbers
    }
}

class OAuthUtil {

    /**
     * RFC3986 URL Encode method
     * @param type $input
     * @return string
     */
    public static function urlencode_rfc3986($input){
        if(is_array($input)) {
            return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
        }elseif(is_scalar($input)){
            return str_replace(
                '+', ' ', str_replace('%7E', '~', rawurlencode($input))
            );
        }else{
            return '';
        }
    }

    // This decode function isn't taking into consideration the above
    // modifications to the encoding process. However, this method doesn't
    // seem to be used anywhere so leaving it as is.
    public static function urldecode_rfc3986($string) {
      return urldecode($string);
    }


    /**
     * URL Parameter parser. This function takes a input like a=b&a=c&d=e and returns the parsed
     * parameters like this an an arry like this: array('a' => array('b','c'), 'd' => 'e')
     * @param type $input
     * @return type
     */
    public static function parse_parameters($input) {
        if(!isset($input) || !$input){ return array(); }

        $pairs = explode('&', $input);

        $parsed_parameters = array();
        foreach ($pairs as $pair) {
            $split = explode('=', $pair, 2);
            $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
            $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

            if(isset($parsed_parameters[$parameter])){
                // We have already recieved parameter(s) with this name, so add to the list
                // of parameters with this name
                if (is_scalar($parsed_parameters[$parameter])) {
                    // This is the first duplicate, so transform scalar (string) into an array
                    // so we can add the duplicates
                    $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
                }
                $parsed_parameters[$parameter][] = $value;
            }else{
                $parsed_parameters[$parameter] = $value;
            }
        }
        return $parsed_parameters;
    }

    public static function build_http_query($params) {
        if(!$params){ return ''; }

        // Urlencode both keys and values
        $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
        $values = OAuthUtil::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if(is_array($value)){
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                sort($value, SORT_STRING);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            }else{
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }
}