<?php
require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;
function getTweetInfo($connection, $tweetid) {
    $tweet = $connection->get('statuses/show', array(
        'id' => $tweetid,
        'tweet_mode' => 'extended',
        'include_entities' => 'true'
    ));
    return $tweet;
}
function getTweetImage($tweet) {
    $sizeOfArray         = count($tweet->extended_entities->media);
    $videoUrls           = Array();
    $videoUrlsIndexPoint = 0;
    for ($i = 0; $i < $sizeOfArray; $i++) {
        $type = $tweet->extended_entities->media[$i]->type;
        if($type=="video"){
            $sizeOfInArray  = count($tweet->extended_entities->media[$i]->video_info->variants);
            for ($j = 0; $j < $sizeOfInArray; $j++) {
                $typeOfContent = $tweet->extended_entities->media[$i]->video_info->variants[$j]->content_type;
                if ($typeOfContent == "video/mp4") {
                    $videoUrls[$videoUrlsIndexPoint]["type"]  = $tweet->extended_entities->media[$i]->video_info->variants[$j]->content_type;
                    $videoUrls[$videoUrlsIndexPoint]["size"]  = videoSize($tweet->extended_entities->media[$i]->video_info->variants[$j]->url);
                    $videoUrls[$videoUrlsIndexPoint]["thumb"] = cleanParametersFromUrl($tweet->extended_entities->media[$i]->media_url_https);
                    $videoUrls[$videoUrlsIndexPoint]["url"]   = cleanParametersFromUrl($tweet->extended_entities->media[$i]->video_info->variants[$j]->url);
                    $videoUrlsIndexPoint++;
                }
            }
        } else if($type=="photo"){
            $videoUrls[$videoUrlsIndexPoint]["type"]  = "image/jpeg";
            $videoUrls[$videoUrlsIndexPoint]["size"]  = videoSize($tweet->extended_entities->media[$i]->media_url_https);
            $videoUrls[$videoUrlsIndexPoint]["thumb"] = cleanParametersFromUrl($tweet->extended_entities->media[$i]->media_url_https);
            $videoUrls[$videoUrlsIndexPoint]["url"]   = cleanParametersFromUrl($tweet->extended_entities->media[$i]->media_url_https);
            $videoUrlsIndexPoint++;
        }
    }
    return $videoUrls;
}
function getTweetText($tweet) {
    return $tweet->full_text;
}
function getTweetVideo($tweet) {
    $sizeOfArray         = count($tweet->extended_entities->media[0]->video_info->variants);
    $videoUrls           = Array();
    $videoUrlsIndexPoint = 0;
    for ($i = 0; $i < $sizeOfArray; $i++) {
        // can be video/mp4 , application/x-mpegURL
        $typeOfContent = $tweet->extended_entities->media[0]->video_info->variants[$i]->content_type;
        if ($typeOfContent == "video/mp4") {
            $videoUrls[$videoUrlsIndexPoint]["type"]    = $tweet->extended_entities->media[0]->video_info->variants[$i]->content_type;
            $videoUrls[$videoUrlsIndexPoint]["size"]    = videoSize($tweet->extended_entities->media[0]->video_info->variants[$i]->url);
            $videoUrls[$videoUrlsIndexPoint]["thumb"]   = cleanParametersFromUrl($tweet->extended_entities->media[0]->media_url_https);
            //$videoUrls[$videoUrlsIndexPoint]["bitrate"] = $tweet->extended_entities->media[0]->video_info->variants[$i]->bitrate;
            $videoUrls[$videoUrlsIndexPoint]["url"]     = cleanParametersFromUrl($tweet->extended_entities->media[0]->video_info->variants[$i]->url);

            //Have to make index point+1 on each object added.
            $videoUrlsIndexPoint++;
        }
    }
    return $videoUrls;
}
function cleanParametersFromUrl($url) {
    // If URL has ? paramters. 
    if (strpos($url, '?') !== false) {
        //Catch and select first part.
        $urlarray = explode("?", $url);
        $newurl   = $urlarray[0];
    } else {
        $newurl = $url;
    }
    return $newurl;
}
function videoSize($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_NOBODY, TRUE);
    $data = curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);
    return round(($size/(1024*1024)), 2);
}
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
    $bytes = max($bytes, 0); 
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow   = min($pow, count($units) - 1); 
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}
function urlsize($url):int{
   return array_change_key_case(get_headers($url,1))['content-length'];
}
?>