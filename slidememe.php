<?php

    // Paths to the external tools (convert from ImageMagick, swfrender from swftools & wget)
    $CONVERT = '~/tools/convert';
    $SWFRENDER = '~/tools/swfrender';
    $WGET = '/usr/bin/wget';
    
    // Directory to store temporary swf and image files
    $TEMP_DIR = '...';
    
    // Site configuration
    $GIF_LAST_PICTURE = '~/slide.memethis.com/end.gif';
    $FINAL_DIR = ".../img.slide.memethis.com/img/";
    $URL_PREFIX = "http://img.slide.memethis.com/img/";
    
    // Just playing it safe...
    $slideshowUrl = $_REQUEST["url"];
    if (strpos($slideshowUrl, "http://www.slideshare.net/")!==0) {
        die("Invalid parameter");
    }

    // Get the title and the slides list, with a little help from YQL
    $yql_title="http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D'".$slideshowUrl."'%20and%20xpath%3D'%2F%2Fhead%2F%2Ftitle'&format=json&diagnostics=false&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
    $yql_slides="http://query.yahooapis.com/v1/public/yql?q=use%20%22http%3A%2F%2Fslide.memethis.com%2Fslideshare.slides.xml%22%3B%20select%20*%20from%20slideshare.slides%20where%20url%3D'".$slideshowUrl."'%3B&format=json";
    $slides_metainfo = json_decode(file_get_contents($yql_slides));
    $title = json_decode(file_get_contents($yql_title))->query->results->title;
 
    $tempprefix = $TEMP_DIR . "img" . uniqid();
    $i = 1;
    if (!$slides_metainfo->query->results->item->Show->Slide) {
        die($_REQUEST["callback"].'('.json_encode(array("error"=>"Sorry, SlideMeme only works with non-multimedia presentations")).')');
    }
    foreach ($slides_metainfo->query->results->item->Show->Slide as $slide) {
        // Let's download each slide's swf and convert it to a png
        // (we'd rather use a GIF, but that's what swfrender can do)
        $swf_name = $tempprefix . $i . ".swf";
        $png_name = $tempprefix . $i . ".png";
        exec($WGET . ' -O ' . $swf_name . ' ' . $slide->Src);
        exec($SWFRENDER . ' -o ' . $png_name . ' ' . $swf_name);
        // 5 slides limit
        if (++$i > 5) {
            break;
        }
    }
    
    // Now we grab the 5 PNGs and the last picture, resize and animate to a gif
    $final_gif_name = "img" . uniqid() . ".gif";
    exec($CONVERT . " -limit memory 64 -limit map 128 -resize 320x240 -delay 200 -loop 0 " . $tempprefix . "*.png "
                  . $GIF_LAST_PICTURE .  " " . $FINAL_DIR . $final_gif_name);

    // Cleanup
    for($i=1; $i<=5; $i++) {
        unlink($tempprefix . $i . ".swf");
        unlink($tempprefix . $i . ".png");
    }

    $result_arr = array(
        "gif" => $URL_PREFIX . $final_gif_name,
        "title" => $title,
        "url" => $slideshowUrl);
                    
    echo $_REQUEST["callback"].'('.json_encode($result_arr).')';
            
?>