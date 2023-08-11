<?php
/**
 * @package waas1-site-settings
 */
/*
Plugin Name: waas1-site-settings
Plugin URI: https://waas1.com/
Description: Settings panel for a site
Version: 1.0.0
Author: Erfan
Author URI: https://waas1.com/
License: GPLv2 or later
*/


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


//only needs to run in the admin area
if( !is_admin() ){
	return;
}


if ( !defined( 'WAAS1_SITE_SETTINGS_NAME' ) ) {
	define( 'WAAS1_SITE_SETTINGS_NAME', 'waas1-site-settings' );
}
if ( !defined( 'WAAS1_SITE_SETTINGS_VERSION' ) ) {
	define( 'WAAS1_SITE_SETTINGS_VERSION', '1.0' );
}


add_action('admin_menu', function(){
	
	if( !defined('WAAS1_RESTRICTION_ALLOW_SITE_SETTINGS_MU_PLUGIN') ){
		define( 'WAAS1_RESTRICTION_ALLOW_SITE_SETTINGS_MU_PLUGIN',	false ); //default not to allow site settings
	}
	if( !WAAS1_RESTRICTION_ALLOW_SITE_SETTINGS_MU_PLUGIN ){
		$user = wp_get_current_user();
		if( $user->user_login != 'superduper' ){
			return false;
		}
	}
	
	
	//otherwise build the plugin menu and html for superduper user.
	
	//build the menu system
	add_submenu_page( 'index.php', 'Site settings', 'Site Settings', 'administrator', WAAS1_SITE_SETTINGS_NAME, 'waas1SiteSettingsInit' );
	
	
	add_action( 'admin_enqueue_scripts', function(){
		// CSS
		wp_register_style( WAAS1_SITE_SETTINGS_NAME.'_dns_css', WP_CONTENT_URL . '/mu-plugins/assets/css/'.WAAS1_SITE_SETTINGS_NAME.'-dns.css', array(), WAAS1_SITE_SETTINGS_VERSION );

		// JS
		wp_register_script( WAAS1_SITE_SETTINGS_NAME.'_dns_script', WP_CONTENT_URL . '/mu-plugins/assets/js/'.WAAS1_SITE_SETTINGS_NAME.'-dns.js', array( 'jquery' ), WAAS1_SITE_SETTINGS_VERSION );
	});

});







function waas1SiteSettingsInit(){
	
	
	$currentTask = null;
	$currentTaskTitle = null;
	
	
	if( isset($_GET['task']) && $_GET['task'] ){
		
		if( $_GET['task'] == 'dns-manager' ){
			$currentTask = 'dns-manager';
			$currentTaskTitle = ' - DNS manager';
		}
		
	}
	
	
	
	$pagehtml = '<div class="wrap">'; //wrapper div starts
		$pagehtml .= '<h1>'.get_admin_page_title().$currentTaskTitle.'</h1>';
		
		//build the menu here
		$pagehtml .= '<p>';
		$pagehtml .= '<a href="'.admin_url( 'index.php?page='.WAAS1_SITE_SETTINGS_NAME.'&task=dns-manager' ).'">DNS Manager</a>';
		$pagehtml .= '</p>';
		
		
		
		

		if( $currentTask == 'dns-manager' ){ //dns manager starts
			
			if( isset($_GET['sub-task']) && $_GET['sub-task'] == 'delete-record' ){//start subtask = delete-record
				if( !isset($_GET['record-id']) && trim($_GET['record-id']) == '' ){//start required id
					
					$pagehtml .= '<div class="notice notice-error is-dismissible"><p>Parameter "record-id" not found.</p></div>';
					
				}else{
					
					$waas1WorkerApi = new waas1WorkerApi( 'dns/delete/', array('record-id'=>$_GET['record-id']) );
					$apiResponse = $waas1WorkerApi->execute();
					
					if( !$apiResponse ){
						$pagehtml .= '<div class="notice notice-error is-dismissible"><p>An Error Occurred ID: #534568</p></div>';
					}else{
						$apiResponse = json_decode( $waas1WorkerApi->getResponse(), true) ;
						if( $apiResponse['status'] == false ){
							$pagehtml .= '<div class="notice notice-error is-dismissible"><p>'.$apiResponse['errorMsg'].'</p></div>';
						}else{
							$pagehtml .= '<div class="notice notice-success"><p><strong>DNS</strong> record deleted.</p></div>';
						}
					}
					
				}//end required id
			}//end subtask = delete-record
			
			
			
			if( isset($_GET['sub-task']) && $_GET['sub-task'] == 'add-record' ){//start subtask = delete-record
				//get all variables
				$dnsRecordType = $_POST['dns-type'];
				$dnsRecordName = $_POST['dns-name'];
				$dnsRecordContent = $_POST['dns-content'];
				$dnsRecordTtl = $_POST['dns-ttl'];
				$dnsRecordProxied = $_POST['dns-proxied'];
				$dnsRecordPriority = $_POST['dns-priority'];
				
				
				$waas1WorkerApi = new waas1WorkerApi( 'dns/add/', array(
														'site-id'=>THIS_SITE_ID, 
														'dns-type'=>$dnsRecordType,
														'dns-name'=>$dnsRecordName,
														'dns-content'=>$dnsRecordContent,
														'dns-ttl'=>$dnsRecordTtl,
														'dns-proxied'=>$dnsRecordProxied,
														'dns-priority'=>$dnsRecordPriority,
														) 
													);
				$apiResponse = $waas1WorkerApi->execute();
				if( !$apiResponse ){
					$pagehtml .= '<div class="notice notice-error is-dismissible"><p>An Error Occurred ID: #5345969</p></div>';
				}else{
					$apiResponse = json_decode( $waas1WorkerApi->getResponse(), true) ;
					if( $apiResponse['status'] == false ){
						$pagehtml .= '<div class="notice notice-error is-dismissible"><p>Make sure DNS type represents the correct contents. For example DNS type A contents can only contains a valid IPV4 address. - '.$apiResponse['errorMsg'].'</p></div>';
					}else{
						$pagehtml .= '<div class="notice notice-success"><p><strong>DNS</strong> record added.</p></div>';
					}
				}
					
				
			}//end subtask = delete-record
			
			
			
			
		
			$pagehtml .= waas1BuildDnsManager();
		}//dns manager ends
		
		
		
		
	$pagehtml .= '</div>';//wrapper div ends
	echo $pagehtml;
}











function waas1BuildDnsManager(){
	
	wp_enqueue_style( WAAS1_SITE_SETTINGS_NAME.'_dns_css' );
	wp_enqueue_script( WAAS1_SITE_SETTINGS_NAME.'_dns_script' );
	
	
	$pagehtml = '';
	
	
	
	
	
	//add dns record form
	$pagehtml .= '<h3>Add new record:</h3>';
	
	$pagehtml .= '<div class="addDnsRecordWrapper"><form action="'.admin_url( 'index.php?page='.WAAS1_SITE_SETTINGS_NAME.'&task=dns-manager&sub-task=add-record' ).'" method="post"><table class="form-table"><tbody>';
	
		$pagehtml .= '<tr>';
		
			$pagehtml .= '<td class="type"><label for="type">Type: <select name="dns-type" id="type">';
				$pagehtml .= '<option value="a">A</option>';
				$pagehtml .= '<option value="aaaa">AAAA</option>';
				$pagehtml .= '<option value="cname">cname</option>';
				$pagehtml .= '<option value="mx">MX</option>';
				$pagehtml .= '<option value="txt">TXT</option>';
			$pagehtml .= '</select></label></td>';
			
			$pagehtml .= '<td class="name"><label for="name">Name: <input id="name" type="text" name="dns-name" value="" placeholder="name.'.REGISTERABLE_DOMAIN.'" /></label></td>';
			
			$pagehtml .= '<td class="content"><label for="content">Content: <input id="content" type="text" name="dns-content" value="" placeholder="Content" /></label></td>';
			
			$pagehtml .= '<td><label for="ttl">TTL: <select name="dns-ttl" id="ttl">';
				$pagehtml .= '<option value="1">AUTO</option>';
				$pagehtml .= '<option value="120">2 min</option>';
				$pagehtml .= '<option value="300">5 min</option>';
				$pagehtml .= '<option value="600">10 min</option>';
				$pagehtml .= '<option value="900">15 min</option>';
				$pagehtml .= '<option value="1800">30 min</option>';
				$pagehtml .= '<option value="3600">1 hr</option>';
				$pagehtml .= '<option value="7200">2 hr</option>';
				$pagehtml .= '<option value="18000">5 hr</option>';
				$pagehtml .= '<option value="43200">12 hr</option>';
				$pagehtml .= '<option value="86400">1 day</option>';
			$pagehtml .= '</select></label></td>';
			
			
			
		
		$pagehtml .= '</tr>';
		$pagehtml .= '<tr>';
			$pagehtml .= '<td class="priority"><label for="priority">Priority: <input id="priority" type="number" name="dns-priority" value="0" placeholder="Priority" /><span class="description"> 0 - 65535</span></label></td>';
	
			$pagehtml .= '<td><label for="proxied">Proxy Record <select name="dns-proxied" id="proxied">';
				$pagehtml .= '<option value="true">Yes - (Proxied)</option>';
				$pagehtml .= '<option value="false">No - (DNS only)</option>';
			$pagehtml .= '</select></label></td>';
			
			$pagehtml .= '<td class="submit"><p class="submit"><input type="submit" value="Add DNS record" class="button-primary" name="Submit"> <br /> if record already found it will be overwritten.</p></td>';
			
		$pagehtml .= '</tr>';
		
	$pagehtml .= '</tbody></table></form></div>';
	
	
	
	
	
	$waas1WorkerApi = new waas1WorkerApi( 'dns/list/', array('site-id'=>THIS_SITE_ID) );
	$apiResponse = $waas1WorkerApi->execute();
	
	$pagehtml .= '<h3>Existing DNS records:</h3>';
	
	if( !$apiResponse ){
		$pagehtml .= '<span>An Error Occurred ID: #532489</span>';
		return $pagehtml;
	}
	
	
	//set the actual body response
	$apiResponse = json_decode( $waas1WorkerApi->getResponse(), true) ;
	if( $apiResponse['status'] == false ){
		$pagehtml .= $apiResponse['errorMsg'];
		return $pagehtml;
	}
	
	$pagehtml .= '<table class="div-table"><tbody>';
	
		$pagehtml .= '<tr class="div-table-row heading">';
			$pagehtml .= '<td class="div-table-col count">&nbsp;</td>';
			$pagehtml .= '<td class="div-table-col type">Type</td>';
			$pagehtml .= '<td class="div-table-col name">Name</td>';
			$pagehtml .= '<td class="div-table-col content">Content</td>';
			$pagehtml .= '<td class="div-table-col ttl">TTL</td>';
			$pagehtml .= '<td class="div-table-col proxied">Proxy status</td>';
			$pagehtml .= '<td class="div-table-col action">&nbsp;</td>';
		$pagehtml .= '</tr>';
		
		
		
		//unset critical records
		foreach( $apiResponse['records'] as $key=>$record ){
			
			//skip the main root domain name if the record type is cname
			if( $record['type'] == 'CNAME' && $record['name'] == REGISTERABLE_DOMAIN ){
				unset( $apiResponse['records'][$key] );
			}
			//skip www version of root domain name
			if( $record['type'] == 'CNAME' && $record['name'] == 'www.'.REGISTERABLE_DOMAIN ){
				unset( $apiResponse['records'][$key] );
			}
			//skip CDN
			if( $record['type'] == 'CNAME' && $record['name'] == CLOUDFLARE_CDN_CNAME.'.'.REGISTERABLE_DOMAIN ){
				unset( $apiResponse['records'][$key] );
			}
			
		}
		
		
		$loopType = 'even';
		
		if( count($apiResponse['records']) === 0 ){
			
			$pagehtml .= '<tr class="div-table-row '.$loopType.'">';
			
				$pagehtml .= '<td class="div-table-col count"></td>';
				$pagehtml .= '<td class="div-table-col type"></td>';
				$pagehtml .= '<td class="div-table-col name"></td>';
				$pagehtml .= '<td class="div-table-col content">No DNS records found. Some critical records are hidden.</td>';
				$pagehtml .= '<td class="div-table-col ttl"></td>';
				$pagehtml .= '<td class="div-table-col proxied"></td>';
				$pagehtml .= '<td class="div-table-col action"></td>';

			$pagehtml .= '</tr>';
			
		}else{
			
			$counter = 1;
			foreach( $apiResponse['records'] as $record ){		

				$pagehtml .= '<tr data-recordid="'.$record['id'].'" class="div-table-row '.$loopType.'">';
					$pagehtml .= '<td class="div-table-col count">'.($counter).'</td>';
					$pagehtml .= '<td class="div-table-col type">'.$record['type'].'</td>';
					$pagehtml .= '<td class="div-table-col name">'.$record['name'].'</td>';
					

					$pagehtml .= '<td class="div-table-col content">';
					$pagehtml .= $record['content'];
					
						if( isset($record['priority']) ){
							$pagehtml .= ' <small>[' .$record['priority']. ']</small>';
						}
					
					$pagehtml .= '</td>';
					
					
					
					
					$pagehtml .= '<td class="div-table-col ttl">'.$record['ttl'].'</td>';
					
					if( $record['proxied'] == '1' ){
						$pagehtml .= '<td class="div-table-col proxied">Proxied</td>';
					}else{
						$pagehtml .= '<td class="div-table-col proxied">DNS only</td>';
					}
					
					$pagehtml .= '<td class="div-table-col action"><a class="deleteRecord" href="#">Delete</a></td>';
					
				$pagehtml .= '</tr>';
				
				if( $loopType == 'even' ){
					$loopType = 'odd';
				}else{
					$loopType = 'even';
				}
				$counter++;
			}//end foreach
		
		}//if else we have records
		
		
	

	$pagehtml .= '</tbody></table>';
	
	
	return $pagehtml;

}



?>