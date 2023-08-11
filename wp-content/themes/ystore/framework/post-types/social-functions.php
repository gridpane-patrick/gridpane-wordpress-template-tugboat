<?php
/**
 * Description
 *
 * @package    social-functions.php
 * @since      8.0.0
 * @author     stas
 * @link       http://xstore.8theme.com
 * @license    Themeforest Split Licence
 */

defined( 'ABSPATH' ) || exit( 'Direct script access denied.' );

// **********************************************************************//
// ! Add Facebook Open Graph Meta Data
// **********************************************************************//
if( ! function_exists( 'etheme_add_opengraph_doctype' ) ) {
	function etheme_add_opengraph_doctype( $output ) {
		$share_facebook = etheme_get_option('socials',array( 'share_twitter', 'share_facebook', 'share_vk', 'share_pinterest', 'share_mail', 'share_linkedin', 'share_whatsapp', 'share_skype'
		));
		if ( is_array($share_facebook) && in_array( 'share_facebook', $share_facebook ) ) {
//			return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
			return $output . ' xmlns="http://www.w3.org/1999/xhtml" prefix="og: http://ogp.me/ns# fb: http://www.facebook.com/2008/fbml"';
		} else {
			return $output;
		}
	}
}
add_filter('language_attributes', 'etheme_add_opengraph_doctype');

// **********************************************************************//
// ! Twitter API functions
// **********************************************************************//
if(!function_exists('etheme_capture_tweets')) {
	function etheme_capture_tweets($consumer_key,$consumer_secret,$user_token,$user_secret,$user, $count) {
		
		$connection = etheme_connection_with_access_token($consumer_key,$consumer_secret,$user_token, $user_secret);
		$params = array(
			'screen_name' => $user,
			'count' => $count
		);
		
		$content = $connection->get("statuses/user_timeline",$params);
		
		return json_encode($content);
	}
}

if(!function_exists('etheme_connection_with_access_token')) {
	function etheme_connection_with_access_token($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret) {
		$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
		return $connection;
	}
}

if(!function_exists('etheme_tweet_linkify')) {
	function etheme_tweet_linkify($tweet) {
		$tweet = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $tweet);
		$tweet = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $tweet);
		$tweet = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweet);
		$tweet = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweet);
		return $tweet;
	}
}

if(!function_exists('etheme_store_tweets')) {
	function etheme_store_tweets($file, $tweets) {
		ob_start(); // turn on the output buffering
		$fo = etheme_fo($file, 'w'); // opens for writing only or will create if it's not there
		if (!$fo) return etheme_print_tweet_error(error_get_last());
		$fr = etheme_fw($fo, $tweets); // writes to the file what was grabbed from the previous function
		if (!$fr) return etheme_print_tweet_error(error_get_last());
		etheme_fc($fo); // closes
		ob_end_flush(); // finishes and flushes the output buffer;
	}
}

if(!function_exists('etheme_pick_tweets')) {
	function etheme_pick_tweets($file) {
		ob_start(); // turn on the output buffering
		$fo = etheme_fo($file, 'r'); // opens for reading only
		if (!$fo) return etheme_print_tweet_error(error_get_last());
		$fr = etheme_fr($fo, filesize($file));
		if (!$fr) return etheme_print_tweet_error(error_get_last());
		etheme_fc($fo);
		ob_end_flush();
		return $fr;
	}
}

if(!function_exists('etheme_print_tweet_error')) {
	function etheme_print_tweet_error($errorsArray) {
		$html = '';
		if( count($errorsArray) > 0 ){
			foreach ($errorsArray as $key => $error) {
				$html .= '<p class="warning">Error: ' . $error['message']  . '</p>';
			}
		}
		return $html;
	}
}

if(!function_exists('etheme_twitter_cache_enabled')) {
	function etheme_twitter_cache_enabled(){
		return apply_filters('etheme_twitter_cache_enabled', true);
	}
}

if(!function_exists('etheme_get_tweets')) {
	function etheme_get_tweets($consumer_key, $consumer_secret, $user_token, $user_secret, $user, $count, $cachetime=50, $key = 'widget') {
		if(etheme_twitter_cache_enabled()){
			//setting the location to cache file
			$cachefile = ETHEME_CODE_DIR . 'cache/cache-twitter-' . $key . '.json';
			
			// the file exitsts but is outdated, update the cache file
			if (file_exists($cachefile) && ( time() - $cachetime > filemtime($cachefile)) && filesize($cachefile) > 0) {
				//capturing fresh tweets
				$tweets = etheme_capture_tweets($consumer_key,$consumer_secret,$user_token,$user_secret,$user, $count);
				$tweets_decoded = json_decode($tweets, true);
				//if get error while loading fresh tweets - load outdated file
				if(isset($tweets_decoded['errors'])) {
					$tweets = etheme_pick_tweets($cachefile);
				}
				//else store fresh tweets to cache
				else
					etheme_store_tweets($cachefile, $tweets);
			}
			//file doesn't exist or is empty, create new cache file
			elseif (!file_exists($cachefile) || filesize($cachefile) == 0) {
				$tweets = etheme_capture_tweets($consumer_key,$consumer_secret,$user_token,$user_secret,$user, $count);
				$tweets_decoded = json_decode($tweets, true);
				//if request fails, and there is no old cache file - print error
				if(isset($tweets_decoded['errors'])) {
					echo etheme_print_tweet_error($tweets_decoded['errors']);
					return array();
				}
				//make new cache file with request results
				else
					etheme_store_tweets($cachefile, $tweets);
			}
			//file exists and is fresh
			//load the cache file
			else {
				$tweets = etheme_pick_tweets($cachefile);
			}
		} else{
			$tweets = etheme_capture_tweets($consumer_key,$consumer_secret,$user_token,$user_secret,$user, $count);
		}
		
		$tweets = json_decode($tweets, true);
		
		if(isset($tweets['errors'])) {
			echo etheme_print_tweet_error($tweets_decoded['errors']);
			return array();
		}
		
		return $tweets;
	}
}

