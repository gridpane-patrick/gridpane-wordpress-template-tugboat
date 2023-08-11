<?php

defined( 'ABSPATH' ) or die( 'Please don&rsquo;t call the plugin directly. Thanks :)' );


add_action( 'wp_trash_post', 'seopress_watcher_post_trash' );

/**
 * Detect a post trash
 * @return void
 * @author Thomas Deneulin
 */
function seopress_watcher_post_trash($post_id)
{

	$can_autoredirect = seopress_can_post_autoredirect($post_id);

	if(!$can_autoredirect){
		return;
	}

	if(wp_is_post_revision($post_id)){
		return;
	}

	$status = get_post_status($post_id);
	if($status !== "publish"){
		return;
	}


	$url = seopress_get_permalink_for_updated_post($post_id);

	$notices = seopress_get_option_post_need_redirects();

	if($notices){
		foreach ($notices as $key => $value) {
			if(isset($value["new_url"]) && $value["new_url"] === $url){
				seopress_remove_notification_for_redirect($value['id']);
			}
		}
	}
	/* translators: %s: post permalink */
	$message =
	sprintf(
		__('<p>We have detected that you have deleted a post (<code>%s</code>).</p>', 'wp-seopress-pro'),
		$url
	);

	$message .= '<p>' . __('We suggest you to redirect this URL to avoid any SEO issues, and keep an optimal user experience.', 'wp-seopress-pro') . '</p>';

	seopress_create_notifaction_for_redirect([
		"id" => uniqid('', true),
		"message" => $message,
		"type" => "delete",
		"before_url" => $url
	]);

}
add_action( 'post_updated', 'seopress_watcher_slug_change', 12, 3 );

/**
 * Detect slug change. Not work in Gutenberg
 *
 * @return void
 * @author Thomas Deneulin
 */
function seopress_watcher_slug_change( $post_id, $post, $post_before ){

	$authorize = true;
	if($post_before->post_status === "trash"){
		$authorize = false;
	}

	$can_autoredirect = seopress_can_post_autoredirect($post_id);

	if(!$can_autoredirect){
		$authorize = false;
	}

	if ( wp_is_post_revision( $post_before ) !== false && wp_is_post_revision( $post ) !== false ) {
		$authorize = false;
	}

	$url_post = seopress_get_permalink_for_updated_post($post);
	$url_post_before = seopress_get_permalink_for_updated_post( $post_before );
	$notices = seopress_get_option_post_need_redirects();

	if($notices){
		foreach ($notices as $key => $value) {
			if(isset($value["new_url"]) && $value["new_url"] === $url_post_before){
				seopress_remove_notification_for_redirect($value['id']);
			}
		}
	}

	// Prevent same slug
	if($url_post === $url_post_before){
		$authorize = false;
	}

	// Prevent {status} to publish
	if($url_post !== $url_post_before && $post_before->post_status !== "publish" && $post->post_status === "publish"){
		$authorize = false;
	}

	$post_status_authorized = [
		'publish',
		'static',
		'private',
	];

	if( !in_array(get_post_status($post->ID), $post_status_authorized, true ) || !in_array(get_post_status($post->ID), $post_status_authorized, true )  ){
		$authorize = false;
	}


	$authorize = apply_filters('seopress_watcher_slug_change_can_create_notification', $authorize, $post_id, $post, $post_before);

	if(!$authorize){
		return;
	}

	/* translators: %s: post name (slug) %s: url redirect */
	$message = sprintf(
		__('<p>We have detected that you have changed a slug (<code>%s</code>) to (<code>%s</code>).</p>', 'wp-seopress-pro'),
		$url_post_before,
		$url_post
	);

	$message .= '<p>' . __('We suggest you to redirect this URL.', 'wp-seopress-pro') . '</p>';

	seopress_create_notifaction_for_redirect([
		"id" => uniqid('', true),
		"message" => $message,
		"type" => "update",
		"before_url" => $url_post_before,
		"new_url" => $url_post
	]);
}


add_filter('trash_to_publish', 'seopress_remove_notice_if_needed');

function seopress_remove_notice_if_needed($post){

	// Remove notice watcher if needed
	$notices = seopress_get_option_post_need_redirects();
	if(!$notices){
		return;
	}

	foreach($notices as $key => $notice){
		if(strpos($notice['before_url'], $post->post_name) !== false){
			seopress_remove_notification_for_redirect($notice['id']);
		}
	}
}
