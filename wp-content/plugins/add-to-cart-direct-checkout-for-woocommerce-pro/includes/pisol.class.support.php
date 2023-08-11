<?php
/**
 * Version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Pisol_Support' ) ) {

class Pisol_Support{

    private $message = false;

    function __construct($data){
            $this->data               = array();
			$this->data['slug']       = $data['slug'];
            $this->data['version']    = $data['version'];
            $this->data['remote_url']    = $data['remote_url'];
            $this->data['promotion_action']    = $data['promotion_action'];
            $this->data['site_url']    = $data['site_url'];

            $this->getMessage();

            add_action('admin_notices', array($this, 'pisolNotification'));
            add_action('admin_notices', array($this, 'teaserPromotion'));
            add_action($this->data['promotion_action'], array($this, 'promotion'));

    }

    function checkSession(){
       
        $name = $this->data['slug'];
        $stored = get_transient( $name);
        if($stored){
            return true;
        }else{
            return false;
        }
    }

    function getSession(){
        $name = $this->data['slug'];
        $stored = get_transient( $name);
        if($stored){
            return $stored;
        }else{
            return false;
        }
    }

    function setSession($message){
        $name = $this->data['slug'];
        set_transient( $name, $message, 86400 );
    }

    function getRemoteMessage(){
        $data = array(
            'method' => 'POST',
            'body' => $this->data
        );
        
        $response = wp_remote_post($this->data['remote_url'], $data);

        if(is_wp_error($response)) return false;

        return json_decode($response['body']);
    }

    function getMessage(){
        if($this->checkSession()){
            $message = $this->getSession();
        }else{
            $message = $this->getRemoteMessage();
            $this->setSession($message);
        }

        if($message){
            if(isset($message->name) && $message->name == $this->data['slug']){
                    $this->message = $message;
                    return;
            }
        }
    }

    function promotion(){
        $response = $this->message;
        if($response){
            if(isset($response->promotion_message)){
                echo "<div class='col-3'>
                        <p>{$response->promotion_message}</p>
                      </div>";
            }
        }
    }

    function versionCheck($received){
        if(version_compare($received, $this->data['version']) == 1){
            return true;
        }
        return false;
    }

   

    function pisolNotification(){
        $response = $this->message;
        if($response){

            if(isset($response->version) && isset($response->update_message) && $this->versionCheck($response->version)){
                $type = 'error'; // there are 2 type info and error 
                echo "<div class='notice notice-{$type} is-dismissible'>
                        <p>{$response->update_message}</p>
                    </div>";
            }
        }
    }

    function teaserPromotion(){
        $response = $this->message;
        if($response){
            if(isset($response->teaser_message)){
                $type = 'info'; // there are 2 type info and error 
                echo "<div class='notice notice-{$type} is-dismissible'>
                        <p>{$response->teaser_message}</p>
                    </div>";
            }
        }
    }
}

}