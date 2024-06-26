<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="video/mp4">
    <title>My camera</title>
</head>
<body>
    <video width="640" height="480" controls>
        <source src="http://localhost:8080/stream.m3u8" type="application/x-mpegURL">
        <!-- Nastavte URL na výstupný stream z FFmpeg, ktorý prevádza UDP na HTTP -->
    </video>
</body>

ffmpeg -i udp://admin:Bitnami0@10.0.0.64:554 -c:v libx264 -f hls -hls_time 4 -hls_list_size 5 /var/www/html/stream.m3u8
ffmpeg -i udp://admin:Bitnami0@10.0.0.64:554 -c:v libx264 -f hls -hls_time 4 -hls_list_size 5 -hls_flags delete_segments -hls_flags omit_endlist /var/www/html/stream.m3u8



<body>
<video width="320" height="240" autoplay controls>
    <source src="udp://10.0.0.64:554" type="video/mp4">
    <object width="320" height="240" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.5.swf">
        <param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.5.swf" /> 
        <param name="flashvars" value='config={"clip": {"url": "udp://10.0.0.64:554", "autoPlay":true, "autoBuffering":true}}' /> 

    </object>
</video>

<?php
/*
// Zadajte URL vášho streamu UDP / RTSP
$streamUrl = 'udp://10.0.0.64:554' #'udp://adresa_vasich/kamery/stream';

// Spustite FFmpeg, aby zobrazil stream
exec("ffmpeg -i $streamUrl -c:v copy -f mp4 -", $output);

// Výstup streamu
echo implode(PHP_EOL, $output);
*/
/*
require_once 'libs/PHP-FFMpeg/autoload.php';
require 'vendor/autoload.php'; // Prípadne prispôsobte cestu k autoload súboru

use FFMpeg\FFMpeg;

$ffmpeg = FFMpeg::create();
$video = $ffmpeg->open('video.mp4');
$video
    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
    ->save('screenshot.jpg');
echo 'Screenshot uložený.';
*/
?>

</body>
</html>
