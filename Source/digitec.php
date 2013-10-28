<?php

include ('vendor/autoload.php');
use Symfony\Component\DomCrawler\Crawler;

$pre_url = 'https://www.digitec.ch';
$url = 'https://www.digitec.ch/Produktsuche1.aspx?suche=' . urlencode($query);

function pre($var) {
        print '<pre>';
        print_r($var);
        print '</pre>';
}

function get_cookie($pre_url) {
    $pre = curl_init();
    curl_setopt($pre, CURLOPT_URL, $pre_url);
    curl_setopt($pre, CURLOPT_HEADER, false);
    curl_setopt($pre, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($pre, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($pre, CURLOPT_COOKIEJAR, 'digitec');
    curl_exec($pre);
    curl_close($pre);
}

function get_data($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'digitec');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    //$result = curl_exec($ch);
    return curl_exec($ch);
    curl_close($ch);
}

if(!file_exists('digitec')) {
    get_cookie($pre_url);
}

$data = get_data($url);
$crawler = new Crawler($data);

$count = $crawler->filter('div.PRODUKT')->count();
if($count == 0) {
   print '<items>
    <item uid="1" arg="'.$pre_url.'">
        <title>No entries for '.$query.' found.</title>
        <subtitle>Digitec.ch öffnen</subtitle>
        <icon>icon.png</icon>
    </item>
</items>';
}
else {
    $titles = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
            return utf8_decode($node->filter('h3')->text());
    });

    $description = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
        return utf8_decode($node->filter('p')->text());
    });

    $price = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
        return utf8_decode($node->filter('h5')->text());
    });

    $img = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
        return utf8_decode($node->filter('img')->attr('src'));
    });

    $id = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
        return utf8_decode($node->filter('.index')->children()->last()->text());
    });

    $av = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
        return utf8_decode($node->filter('.verf')->children()->text());
    });

    /*
    $link = $crawler->filter('div.PRODUKT')->each(function (Crawler $node, $i) {
        return htmlentities($node->filter('.detail_link')->children()->attr('href'));
    });
    */

    echo '<items>';
    foreach($titles as $i => $title) {
            $c = $i+1;
            echo '  <item uid="'.$c.'" arg="'.$pre_url.'/?param=suche&amp;wert='.$id[$i].'">';
            echo '      <title>'.$title.'</title>';
            echo '      <subtitle>'.$price[$i].'</subtitle>';
            echo '      <icon>icon.png</icon>';
            echo '  </item>';
    }
    echo '</items>';
}

?>
