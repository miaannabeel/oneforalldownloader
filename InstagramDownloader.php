<?php
class InstagramDownloader
{
    public function process($url){
        $return = array();
        $data   = $this->parser($this->url_get_contents($url));
        print_r($data);
        if(isset($data->entry_data)){
            if(isset($data->entry_data->PostPage[0]->graphql->shortcode_media)){
                $shortcode_media = $data->entry_data->PostPage[0]->graphql->shortcode_media;
                if($shortcode_media->__typename == "GraphSidecar"){
                    $edges = $data->entry_data->PostPage[0]->graphql->shortcode_media->edge_sidecar_to_children['edges'];
                    foreach ($edges as $edge) {
                        if($edge->node->__typename == "GraphVideo"){
                            $return[] = array(
                                "type" => "video/mp4",
                                "thumb" => $edge->node->display_url,
                                "url" => $edge->video_url,
                                "size" => $this->videoSize($edge->video_url)
                            );
                        } else if($edge->node->__typename == "GraphImage"){
                            foreach ($edge->node->display_resources as $edge_in) {
                                $return[] = array(
                                    "type" => "image/jpeg",
                                    "thumb" => $edge_in->src,
                                    "url" => $edge_in->src,
                                    "size" => $this->videoSize($edge_in->src)
                                );
                            }
                        }
                    }
                } else if($shortcode_media->__typename == "GraphImage"){
                    $edges = $data->entry_data->PostPage[0]->graphql->shortcode_media->display_resources;
                    foreach ($edges as $edge) {
                        $return[] = array(
                            "type" => "image/jpeg",
                            "thumb" => $data->entry_data->PostPage[0]->graphql->shortcode_media->display_url,
                            "url" => $edge->src,
                            "size" => $this->videoSize($edge->src)
                        );
                    }
                } else if($shortcode_media->__typename == "GraphVideo"){
                    $return[] = array(
                        "type" => "video/mp4",
                        "thumb" => $data->entry_data->PostPage[0]->graphql->shortcode_media->display_url,
                        "url" => $data->entry_data->PostPage[0]->graphql->shortcode_media->video_url,
                        "size" => $this->videoSize($data->entry_data->PostPage[0]->graphql->shortcode_media->video_url)
                    );
                }
            }
        } 
        return $return;
    }
    public function url_get_contents($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.47 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
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
    public function parser($html){
        preg_match('/<script type="text\/javascript">window\._sharedData =([^;]+);<\/script>/', $html, $matches);
        if (!isset($matches[1])){
            return array();
        }
        return json_decode($matches[1]);
    }
}