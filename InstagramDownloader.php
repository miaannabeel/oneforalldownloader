<?php
class InstagramDownloader
{
    public function curlWithCookie($url) {
        $arrSetHeaders = array(
            'authority: www.instagram.com',
            'method: GET',
            'scheme: https',
            'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'accept-encoding: deflate, br',
            'accept-language: en-US,en;q=0.9',
            'cookie: ig_did=25E028A5-8A30-4531-8DEE-494C50351030; mid=XnpG8QALAAH1t2tIgNQ26kfPyEER; ds_user_id=3516169511; fbm_124024574287414=base_domain=.instagram.com; shbid=10196; csrftoken=MSzVBQHcRCnXqhMt0zasVtSNJO4qW8Ao; rur=FTW; sessionid=3516169511%3APkx6Wq0vY7aHVF%3A4; shbts=1621299921.5772078; fbsr_124024574287414=3UoOwvZL2OjPSUtQgFHBDeEufLA76wKgrbfzxG6nV5U.eyJ1c2VyX2lkIjoiMTAwMDAyNzE0NzIyOTAwIiwiY29kZSI6IkFRQ2gtYVphMVBIZ3JlUkVEVVBPamF0WXR6R3VRS0J3Tnd4OXF2amVGVUxtMUhuU0hXMzdUdng5ZFhVNXdpcHZYdkdpcGJvaG9JTW14cGxHRnIyVE1LRC15aXJ0SGZwYXZKYlN0UEU3TjIzOWVzbWxlUjhrNkVSUTd2Tm5hWndyM3N2THJJZVVxOG9GTTBMbmw2ZE45WURwazhkZm5rb3MwZDhBamJXUmQweXVQSGdMOFk2NXFnWWdZSlpfdDNrY2IwdXVsVERvMGFZYmhKbG5zS0V1LWJfRzVnWmZiOVhsTDNLRXNlVkFSSVJkc2NCVm9RamNnTU5XM19BSzRPNTBULWRjRlQ0MmYyd1VsMjU5NzBDMF9IT21GT3FUQTNrLXRGWndjNm5jQmlITWwydEtJVDE0Z3lKWUlLX0ZlbWxwVjhEYktXejREY2xwRGZMZFROdVdveHNpIiwib2F1dGhfdG9rZW4iOiJFQUFCd3pMaXhuallCQUo0MkhwbjdwR1FHTThna2lURTFtM2FqRkRQME56TEZCVzZJN2tVNXFtaFZRU2xOMVhkR0Y3UlBGcEJwYTZENzg4WkFZdm42U21GYXNMbGZUOTBLWkJiVXJRZXNXYkc0bHNJek5aQnJvcnpNS2ZJN0FtUUhBZ2Z2a1VCZTNyOExOYjBsY0ZXVGFUa1pBYTJDYlRsRVVhbEd6TVhzWkNrYWNmSHpEanp3UlpDZ2V5V1VLV2E2Y1pEIiwiYWxnb3JpdGhtIjoiSE1BQy1TSEEyNTYiLCJpc3N1ZWRfYXQiOjE2MjEzMzk5Njh9',
            'sec-ch-ua: " Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
            'sec-ch-ua-mobile: ?0',
            'sec-fetch-dest: document',
            'sec-fetch-mode: navigate',
            'sec-fetch-site: none',
            'sec-fetch-user: ?1',
            'upgrade-insecure-requests: 1',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36',
        );
        $ch            = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $arrSetHeaders);
        curl_setopt($ch, CURLOPT_URL, $url);
        $page          = curl_exec($ch);
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    curl_close($ch);
                    preg_match('/window\.__additionalDataLoaded\([^{]+.*"graphql":({.*}).*}\);<\/script>/', $page, $matches);
                    if (!isset($matches[1])){
                        return array();
                    }
                    return json_decode($matches[1]);
                default:
                    return array();
                    curl_close($ch);
                    break;
            }
        }
    }
    public function process($url){
        $return = array();
        $data   = $this->curlWithCookie($url);
        if(isset($data->shortcode_media)){
            $shortcode_media = $data->shortcode_media;
            if($shortcode_media->__typename == "GraphSidecar"){
                $edges = $shortcode_media->edge_sidecar_to_children->edges;
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
                $edges = $shortcode_media->display_resources;
                foreach ($edges as $edge) {
                    $return[] = array(
                        "type" => "image/jpeg",
                        "thumb" => $shortcode_media->display_url,
                        "url" => $edge->src,
                        "size" => $this->videoSize($edge->src)
                    );
                }
            } else if($shortcode_media->__typename == "GraphVideo"){
                $return[] = array(
                    "type" => "video/mp4",
                    "thumb" => $shortcode_media->display_url,
                    "url" => $shortcode_media->video_url,
                    "size" => $this->videoSize($shortcode_media->video_url)
                );
            }
        } 
        return $return;
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