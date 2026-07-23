<?php

$array_links = [
    'https://www.efrumos.md/onlyChangedExchange',
    'https://www.efrumos.md/generategoodsxml?list=all_b2c',
    'https://www.efrumos.md/generategoodsxml?list=all_b2c_cotril',
    'https://www.efrumos.md/generategoodsxml?list=all_b2c_runail',
];

if(!empty($array_links) && count($array_links)){
    foreach ($array_links as $one_link){
        file_get_contents_curl($one_link);
        sleep(10);
    }
}
$current_hour = intval(date('H'));
if($current_hour == 2){//at two o'clock download full update
    sleep(20);
    $full_exchange_url = 'https://www.efrumos.md/fullExchange/download';
    file_get_contents_curl($full_exchange_url);
}
if($current_hour == 3){//at three o'clock run full update
    sleep(20);
    $full_exchange_url = 'https://www.efrumos.md/fullExchange/update';
    file_get_contents_curl($full_exchange_url);
}
if($current_hour == 4){//at four o'clock run sitemap
    sleep(20);
    $sitemap_url = 'https://www.efrumos.md/generatesitemap';
    file_get_contents_curl($sitemap_url);
}
if($current_hour == 5){//at five o'clock update names ro
    sleep(20);
    $sitemap_url = 'https://www.efrumos.md/ro/parsesubjectsname';
    file_get_contents_curl($sitemap_url);
}
if($current_hour == 6){//at six o'clock update names ru
    sleep(20);
    $sitemap_url = 'https://www.efrumos.md/ru/parsesubjectsname';
    file_get_contents_curl($sitemap_url);
}


function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}