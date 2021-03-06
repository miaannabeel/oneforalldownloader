<?php 
	error_reporting(E_ALL);
	header('Content-Type: application/json');
	require "vendor/autoload.php";
	require "functions.php";
	require_once 'InstagramDownload.php';
	require_once 'InstagramDownloader.php';
	require_once 'FacebookDownload.php';
	use Ayesh\InstagramDownload\InstagramDownload;
	use Abraham\TwitterOAuth\TwitterOAuth;
	$url    = trim($_REQUEST["url"]);
	$domain = str_ireplace("www.", "", parse_url($url, PHP_URL_HOST));
	switch ($domain) {
		case 'facebook.com':
			$video = new FacebookDownload();
			echo json_encode($video->process($url));
			break;
		case 'instagram.com':
			$video = new InstagramDownloader();
			echo json_encode($video->process($url));
			break;
		case 'twitter.com':
			$connection = new TwitterOAuth("S6tc9bCXnA8CeGy8wOBTu98LF", "9O7YNsviXvjZdrVbj0sGjTrIUfv5L5hdGDi1D1UoKylgcv3gDT", "204253793-L80p6Tu1mR6AmgzP1GwWUPtBdJpDF8dafiEaDRE4", "XzRcrCbqM3G2DQyWkOmBgANhMNBlIsRaejfy6k8RNqTwC");
			$content  = $connection->get("account/verify_credentials");
			$tweet_id = (isset($_REQUEST['url']) && $_REQUEST['url']!="") ? basename($_REQUEST['url']) : die(json_encode(array("error"=>"please provide a url")));
			$tweet    = getTweetInfo($connection, $tweet_id);
			$video    = getTweetImage($tweet);
			echo json_encode($video);
			break;
		default:
			echo json_encode(array(
		        "type" => "",
		        "thumb" => "",
		        "url" => "",
		        "size" => ""
		    ));
			break;
	}

	if(isset($_REQUEST['phpinfo'])){
		phpinfo();
	}