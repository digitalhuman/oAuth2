<?php
/**
 * Description of Auth2Class
 * 
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright 2014 Ignite the Future
 * 
 */

require_once(getcwd()."/Curl.php");
require_once(getcwd()."/Redirect.php");

/**
 * Client oAuth2Validate class to validate users
 * 
 * Facebook APP console: https://developers.facebook.com/apps/
 * GMail APP console: https://console.developers.google.com
 * Microsoft Live console: https://account.live.com/developers/applications/
 * LinkedIN console: https://www.linkedin.com/secure/developer
 * 
 */
class oAuth2Validate {
    
    /**
     * Our settings
     * @var type 
     */
    private $settings;

    /**
     * Set to private because we are using static
     */
    public function __construct(oAuth2Settings $settings = null) {
        if($settings !== null){
            $this->settings = $settings;
        }
    }
    
    /**
     * Authenticate with FitBit
     * @param oAuth2Settings $settings
     */
    public function FitBit(oAuth2Settings $settings){
        
        if($settings !== null || $this->settings !== null){
            
            $settings = ($this->settings == null ? $settings : $this->settings);
            
            //Step 1. Get the the secret code from FitBit and let the user authenticate our app.    
            if(!isset($_GET["code"]) && !isset($_GET["token"])){
                
                //Prepare URL
                $url = "https://www.fitbit.com/oauth2/authorize?response_type=code&client_id={$settings->client_id}&"
                . "redirect_uri={$settings->redirect_uri}&scope=activity%20nutrition%20heartrate%20location%20nutrition%20profile%20settings%20sleep%20social%20weight";
                Redirect::To($url);
            }
            
            //Step 2. Get our access_token with the secret code we received from Facebook
            if(isset($_GET['code']) && $_GET['code'] != ""){
                
                $url = "https://api.fitbit.com/oauth2/token?";
                
                $res = Curl::AuthorizedPost($url, base64_encode($settings->client_id.":".$settings->client_secret), array(
                    "client_id"     => $settings->client_id,
                    "grant_type"    => "authorization_code",
                    "redirect_uri"  => $settings->redirect_uri,
                    "code"          => $_GET["code"]
                ));
                
                if(isset($res["access_token"]) && $res["access_token"] != ""){
                    Redirect::To(urldecode($settings->redirect_uri)."?token={$res["access_token"]}");
                }      
                
            }elseif(isset($_GET['token']) && $_GET['token'] != ""){

                //Get user profile
                $url = "https://api.fitbit.com/1/user/-/profile.json";
                $res = Curl::FitBitGet($url, $_GET['token']);
                
                echo "<pre>";
                print_r($res);
            }
            
        }else{
            echo "No settingsobject found.<br/>";
        }
    }
    
    /**
     * Authenticate user through Facebook
     * @param oAuth2Settings $settings
     */
    public function Facebook(oAuth2Settings $settings){
        if($settings !== null || $this->settings !== null){
            
            //
            $settings = ($this->settings == null ? $settings : $this->settings);
            
            //Step 1. Get the the secret code from facebook and let the user authenticate our app.            
            if(!isset($_GET["code"]) && !isset($_GET["token"])){
                
                //Prepare URL
                $url = "https://graph.facebook.com/oauth/authorize?client_id={$settings->client_id}&"
                . "redirect_uri={$settings->redirect_uri}";
                
                Redirect::To($url);
            }
                
            //Step 2. Get our access_token with the secret code we received from Facebook
            if(isset($_GET['code']) && $_GET['code'] != ""){
    
                $url = "https://graph.facebook.com/oauth/access_token?";
                $url .= "client_id={$settings->client_id}&client_secret={$settings->client_secret}&code={$_GET["code"]}&redirect_uri={$settings->redirect_uri}";
                
                $data = Curl::Get($url);
                if(!is_array($data) && !is_object($data)){
                    parse_str($data);
                }else{
                    echo "<pre>".print_r($data, true)."</pre>";
                    die();
                }
                
                if(isset($access_token) && $access_token !== ""){
                    Redirect::To(urldecode($settings->redirect_uri)."?token={$access_token}");
                }

            //Get all the fun we can have
            }elseif(isset($_GET['token']) && $_GET['token'] != ""){

                //Get uses profile
                $url = "https://graph.facebook.com/me?access_token={$_GET["token"]}";
                $res2 = Curl::Get($url);

                echo "<pre>".print_r($res2, true)."</pre>";

            }
        }else{
            echo "No settingsobject found.<br/>";
        }
    }
    
    /**
     * Authenticate through GMail
     * @param oAuth2Settings $settings
     */
    public function GMail(oAuth2Settings $settings){
        if($settings !== null || $this->settings !== null){
            $settings = ($this->settings == null ? $settings : $this->settings);
            
            //Step 1. Get the 'code' with our settings to ask for the access_token
            if(!isset($_GET['code']) && !isset($_GET["access_token"]) && !isset($_GET["token"])){
                
                Redirect::to("https://accounts.google.com/o/oauth2/auth?client_id={$settings->client_id}&"
                . "redirect_uri={$settings->redirect_uri}&"
                . "response_type=code&"
                . "scope=https://www.googleapis.com/auth/contacts.readonly&"
                . "state=%2fprofile&"
                . "include_granted_scopes=true");
                
            //Step 2. Ask for the access token to finalize our request
            }elseif(isset($_GET["code"]) && $_GET["code"] != ""){
                
                $params = "client_id={$settings->client_id}&"
                . "code={$_GET["code"]}&client_secret={$settings->client_secret}&"
                . "redirect_uri={$settings->redirect_uri}&grant_type=authorization_code";

                $res = Curl::Post("https://accounts.google.com/o/oauth2/token", $params);
                if(isset($res["access_token"]) && $res["access_token"] != ""){
                    
                    Redirect::to($settings->redirect_uri."?token={$res["access_token"]}");
                    
                }else{
                    echo $res["error_description"];
                }
            
            //Step 3. Get some contact information
            }elseif(isset($_GET["token"]) && $_GET["token"] != ""){
                
                $res = Curl::Get("https://www.google.com/m8/feeds/contacts/default/full?access_token={$_GET["token"]}");
                echo "<pre>".print_r($res, true)."</pre>";
            }
        }
    }
    
    /**
     * Authenticate through Microsoft Live accounts
     * @param oAuth2Settings $settings
     */
    public function Live(oAuth2Settings $settings){
        if($settings !== null || $this->settings !== null){
            $settings = ($this->settings == null ? $settings : $this->settings);
            
            //Step 2. Fix our url so we can use GET
            if(!isset($_GET["access_token"]) && isset($_SESSION["mslive"])){
                echo '<script type="text/javascript">
                        if(document.URL.indexOf("#") > 0){
                            document.location.href = document.URL.replace("#", "?");
                        }
                    </script>';
            
            //Step 3. Get the address book
            }elseif(isset($_GET["access_token"]) && $_GET["access_token"] != ""){
                
                $res = Curl::Get("https://apis.live.net/v5.0/me/contacts?access_token={$_GET["access_token"]}");
                if(isset($res['data']) && is_array($res['data'])){
                    
                    $addresses = array();
                    $contacts = $res['data'];
                    foreach($contacts as $contact){
                        $address = array();
                        $address["name"] = $contact["name"];
                        $address["email"] = $contact["emails"]["preferred"];
                        array_push($addresses, $address);
                    }

                    echo "<pre>";
                    print_r($addresses);
                }else{
                    echo "Invalid response";
                }
                
            //Step 1. Get our access token. Yes, Microsoft has only 2 steps :)
            }else{
                
                $url = "https://login.live.com/oauth20_authorize.srf?"
                        . "client_id={$settings->client_id}&"
                        . "scope=wl.basic,wl.emails,wl.contacts_emails&"
                        . "response_type=token&"
                        . "redirect_uri={$settings->redirect_uri}";
                        
                //We need this to check where we are in the process
                $_SESSION["mslive"] = 1;
                Redirect::To($url);
                
            }
        }
    }
    
    /**
     * Authenticate our user through LinkedIN
     * @param oAuth2Settings $settings
     */
    public function LinkedIN(oAuth2Settings $settings){
        if($settings !== null || $this->settings !== null){
            $settings = ($this->settings == null ? $settings : $this->settings);
            
            //Step 1. Get the the secret code from facebook and let the user authenticate our app.            
            if(!isset($_GET["code"]) && !isset($_GET["token"])){
                
                $csrf = md5(rand(1,2342342));
                $url = "https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id={$settings->client_id}&"
                . "scope=r_fullprofile%20r_emailaddress&state={$csrf}&redirect_uri={$settings->redirect_uri}";
                Redirect::to($url);

            }
                
            //Step 2. Get our access_token with the secret code we received from Facebook
            if(isset($_GET['code']) && $_GET['code'] != "" && isset($_GET['state'])){
    
                $url = "https://www.linkedin.com/uas/oauth2/accessToken?";
                $res = Curl::Post($url, "grant_type=authorization_code&code={$_GET['code']}&redirect_uri={$settings->redirect_uri}&"
                . "client_id={$settings->client_id}&client_secret={$settings->client_secret}");

                if(isset($res['access_token']) && $res["access_token"] != ""){
                    Redirect::To(urldecode($_SESSION["linkedin_redirect"])."?token={$res["access_token"]}");
                }

            //Step 3. Get all the fun
            }elseif(isset($_GET['token']) && $_GET['token'] != ""){

                //Get user profile
                $url = "https://api.linkedin.com/v1/people/~:(id,first-name,last-name,email-address)?oauth2_access_token={$_GET["token"]}";
                $res2 = Curl::Get($url);

                echo "<pre>".print_r($res2, true)."</pre>";

            }
        }else{
            echo "No settingsobject found.<br/>";
        }
    }
    
    /**
     * Authenticate using Twitter
     * @param oAuth2Settings $settings
     * @todo Login with Twitter
     */
    public function Twitter(oAuth2Settings $settings){
        if($settings !== null || $this->settings !== null){
            $settings = ($this->settings == null ? $settings : $this->settings);
            
//                $output = "";
//                
//                $oauth_nonce = md5(rand(1,2342352342));                
//                $url = "https://api.twitter.com/oauth2/token";
//                
//                $application_key = base64_encode(urlencode($settings->consumer_key).":".urlencode($settings->consumer_secret));
//
//                $req = Curl::TwitterPost($url, $output, $application_key);
//                if(isset($req['access_token'])){
//                    
//                }
        }
    }
    
    /**
     * Authenticate using Dropbox
     * @param oAuth2Settings $settings
     */
    public function Dropbox(oAuth2Settings $settings){
        if($settings !== null || $this->settings !== null){
            $settings = ($this->settings == null ? $settings : $this->settings);
            
            

        }
    }
}

class oAuth2Settings
{
    /**
     * The application id | client id
     * @var string 
     */
    public $client_id;
    
    /**
     * The application secret | client secret
     * @var string 
     */
    public $client_secret;
    
    /**
     * The redirect url to use. This url should be registered with the app.
     * @var string
     */
    public $redirect_uri;
    
    /**
     * Special for Twitter
     * @var type 
     */
    public $access_token = "";    
    public $access_token_secret = "";
    public $consumer_key = "";
    public $consumer_secret = "";
    
    
    public function __get($name) {
        return $name;
    }
    
    public function __set($name, $value) {
        $this->$name = $value;
    }
}