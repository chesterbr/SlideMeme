<?php

    $CONVERT = '"C:\\Program Files\\ImageMagick-6.6.0-Q16\\convert.exe"';
    $URL_PREFIX = "http://img.slide.memethis.com/";
    
    $slideshowUrl = $_REQUEST["url"];
    if (strpos($slideshowUrl, "http://www.slideshare.net/")!==0) {
        die("Invalid parameter");
    }

    $yql_title="http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20html%20where%20url%3D'".$slideshowUrl."'%20and%20xpath%3D'%2F%2Fhead%2F%2Ftitle'&format=json&diagnostics=false&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=";
    $yql_slides="http://query.yahooapis.com/v1/public/yql?q=use%20%22http%3A%2F%2Fslide.memethis.com%2Fslideshare.slides.xml%22%3B%20select%20*%20from%20slideshare.slides%20where%20url%3D'".$slideshowUrl."'%3B&format=json";
    $slides_metainfo = json_decode(file_get_contents($yql_slides));
    $title = json_decode(file_get_contents($yql_title))->query->results->title;
 
    $tempprefix = "img/img".uniqid();
    $i = 1;
    foreach ($slides_metainfo->query->results->item->Show->Slide as $slide) {
        $filename = $tempprefix.$i.".gif";
        $ch = curl_init("http://localhost/swf2gif.asp?url=" . $slide->Src);
	$fp = fopen($filename, "w");
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
        $i++;
        if ($i>5) break;
    }
    
    $cmd = $CONVERT." -delay 200 -loop 0 ".$tempprefix."*.gif img/end.gif ".$tempprefix.".gif";
    //shell_exec($cmd);
    $WshShell = new COM("WScript.Shell");
    $output = $WshShell->Exec($cmd)->StdOut->ReadAll;

    for($i=1; $i<=5; $i++) {
        unlink($tempprefix.$i.".gif");
    }

    $result_arr = array(
        "gif" => $URL_PREFIX.$tempprefix.'.gif',
        "title" => $title,
        "url" => $slideshowUrl);
                    
    echo $_REQUEST["callback"].'('.json_encode($result_arr).')';
            
?>
