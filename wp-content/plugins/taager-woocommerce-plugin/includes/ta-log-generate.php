<?php
defined( 'ABSPATH' ) || exit;


if( ! class_exists( 'Taager_Log_Controller' ) ) {
	
	class Taager_Log_Controller {
		
		
		public function generate($update_type, $ta_version)
		{
			global $current_user;
			$token = get_option( 'ta_log_token' );
			$token_decode = base64_encode( $token );
			$authorization = "Authorization: Bearer ".$token_decode;
			
			//$api_url = 'http://5.l5jn4waluc-xlm41ep8k3dy.p.runcloud.link/log/generate.php';
			$api_url = 'https://taager.xyz/log/generate.php';
			
			$site_name = '&site_name=' . urlencode( get_bloginfo( 'name' ) );
			$site_url   = '&site_url=' . urlencode( get_site_url() );
			$username = '&username=' . urlencode( $current_user->user_login );
			$ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
			$userip = '&userip=' . urlencode( $ip );
			$plugin_version = '&plugin_version=' . urlencode( $ta_version );
			
			$curl = curl_init();
			 
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $api_url . '/?update_type='.$update_type . $site_name . $site_url . $username . $userip . $plugin_version,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				$authorization,
				"cache-control: no-cache"
			  ),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			 
			curl_close($curl);
			 
			if ($err) {
				echo "cURL Error #:" . $err;
			} else {
				echo $response;
			} 
		}
	
	}
	
	new Taager_Log_Controller();
	
}

?>
