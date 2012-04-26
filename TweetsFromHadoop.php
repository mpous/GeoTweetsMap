  <?php

    $url = //URL
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); //maneja los 301, etc.
    $station_array = curl_exec( $ch );

/*
<marker title="Thu, 01 Mar 2012 23:58:37 +0000" lat="41.385" lng="2.1705" />
*/

    echo $station_array;

  ?>
