<?php
class FacebookDownload
{
    public function process($url)
    {
        $data   = $this->url_get_contents($url);
        $hdlink = $this->hdLink($data);
        $sdlink = $this->sdLink($data);
        $thumb  = $this->thumb($data);
        $title  = $this->getTitle($data);
        $return = array();
        if(isset($sdlink)){
          $return[] = array(
              "type" => "video/mp4",
              "thumb" => $thumb,
              "url" => $sdlink,
              "size" => $this->videoSize($sdlink)
          );
        }
        if(isset($hdlink)){
          $return[] = array(
              "type" => "video/mp4",
              "thumb" => $thumb,
              "url" => $hdlink,
              "size" => $this->videoSize($hdlink)
          );
        }
        return $return;
    }
    public function url_get_contents($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function hdLink($curl_content) {
        $regex = '/hd_src:"([^"]+)"/';
        if (preg_match($regex, $curl_content, $match)) {
            return $match[1];
        } else {
            return;
        }
    }
    public function thumb($curl_content) {
        $regex = '/"thumbnailUrl":"([^"]+)"/';
        if (preg_match($regex, $curl_content, $match)) {
            $temp = str_replace("\\", "", urldecode($match[1]));
            return $temp;
        } else {
            return;
        }
    }
    public function sdLink($curl_content) {
        $regex = '/sd_src_no_ratelimit:"([^"]+)"/';
        if (preg_match($regex, $curl_content, $match1)) {
            return $match1[1];
        } else {
            return;
        }
    }
    public function cleanStr($str) {
        return html_entity_decode(strip_tags($str), ENT_QUOTES, 'UTF-8');
    }
    public function getTitle($curl_content) {
        $title = null;
        if (preg_match('/h2 class="uiHeaderTitle"?[^>]+>(.+?)<\/h2>/', $curl_content, $matches)) {
            $title = $matches[1];
        } elseif (preg_match('/title id="pageTitle">(.+?)<\/title>/', $curl_content, $matches)) {
            $title = $matches[1];
        }
        return $this->cleanStr($title);
    }
    public function videoSize($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return round(($size/(1024*1024)), 2);
    }
}
$video = new FacebookDownload();
      echo json_encode($video->process("https://www.facebook.com/1389805397707730/videos/159297806147111"));