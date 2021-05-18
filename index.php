<?php
	header('Content-Type: application/json');
	require "vendor/autoload.php";
	require "functions.php";
	require_once 'InstagramDownload.php';
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
			try {
			    $client = new InstagramDownload($url);
			    $url    = $client->getDownloadUrl();
			    $type   = $client->getType();
			    $thumb  = $client->getThumbUrl();
			    if ($type == "image") {
			        $type = "video/mp4";
			    } else if ($type == "video") {
			        $type = "video/mp4";
			    }
			    echo json_encode(array(
			        "type" => $type,
			        "thumb" => $thumb,
			        "url" => $url,
			        "size" => videoSize($url)
			    ));
			}
			catch (\InvalidArgumentException $exception) {
			    $error = $exception->getMessage();
			    echo $error;
			}
			catch (\RuntimeException $exception) {
			    $error = $exception->getMessage();
			    echo $error;
			}
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