<?php

require('simple_html_dom.php');

// Create DOM from URL or file
$html = file_get_html('https://www.youtube.com/feed/trending?gl=DE');

// creating an array of elements
$videos = [];

// Find top ten videos
$i = 1;
foreach ($html->find('li.expanded-shelf-content-item-wrapper') as $video) {
        if ($i > 100) {
                break;
        }

        // Find item link element
        $videoDetails = $video->find('a.yt-uix-tile-link', 0);

        // get title attribute
        $videoTitle = $videoDetails->title;

        // get href attribute
        $videoUrl = 'https://youtube.com' . $videoDetails->href;

        // Find item description element
        $videoDesc = $video->find('div.yt-lockup-description', 0);

        // Find item description element
        $videoMeta = $video->find('ul.yt-lockup-meta-info', 0);

        // push to a list of videos
        $videos[] = [
                'title' => $videoTitle,
                'link' => $videoUrl,
                'description' => $videoDesc->innertext,
                'date' => $videoMeta->children(0)->innertext,
                'views' => $videoMeta->children(1)->innertext,
        ];

        $i++;
}

?>
<html>
<pre>
<?php
var_dump($videos);
?>
</pre>
</html>