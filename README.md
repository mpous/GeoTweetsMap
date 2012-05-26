GeoTweetsMap
============

Geolocate tweets from specific location with Hadoop, Hive and GoogleMaps. 

This source is the result of doing fast prototyping to capture the MWC 2012 tweets in Barcelona with a 4square link inside.

To run the code, it's necessary to implement a cron to call the file getTweets.php every 5 minutes:

*/5 * * * * curl [your_url]/getTweets.php

Every 5 minutes we will receive a picture of the tweets with 4square check-ins, in this case at Barcelona. We will store this data in a JSON file at the files folder.

After gathering all the information, we will use Hadoop and Hive to process the information and visualize it in a map. At this point I expect that your Hadoop and Hive is working properly. 

We use Hive in order to experiment with Hadoop using the SQL-like language instead of map-reduce.

hive -S -e "select get_json_object(msg, '$.results.geo.coordinates'), get_json_object(msg, '$.results.created_at[0]') from tweets" > result

or

hive -S -e "select get_json_object(msg, '$.results.geo.coordinates'), get_json_object(msg, '$.results.location'), get_json_object(msg, '$.results.created_at[0]') from tweets" > result

This hive command returns us (e.g.):

...
[41.3343,2.05,41.3828,2.1673,41.3733,2.1518,41.3826,2.1635,41.3967,2.1747,41.5512,2.2475]       
["Rambla de Canaletes, Barcelona","Rambla de Canaletes, Barcelona","Rambla de Canaletes, Barcelona","Barcelona","Avinguda Diagonal, 545 - CC L'","Rambla de Canaletes, Barcelona","Passeig de la Vall d'Hebron, 1","Barcelona","Jordi Girona, 29, Barcelona"] 
Mon, 27 Feb 2012 12:44:14 +0000
...

Afterthat we are able to visualize these information in a map to understand the tweets with 4sq checkins at Barcelona during the days of an important event such as Mobile World Congress 2012 (MWC 2012).

> drawHeatmapResult.php > markers

The markers file will contain these information:

...
<marker location="Rambla de Canaletes, Barcelona" title="Mon, 27 Feb 2012 12:04:12 +0000" lat="41.4706" lng="2.0837" />
....

With these data we are able to visualize it easily with Google Maps. The mapping file should need this Javascript code:

function loadMarkers() {
    $.get('tweetsFromHbase.php', function(data){
      $(data).find("marker").each(function() {
        var marker = $(this);
        var latlng = new google.maps.LatLng(
          parseFloat(marker.attr("lat")),
          parseFloat(marker.attr("lng"))
        );
        var title=marker.attr("location")+" : "+marker.attr("title");

        marker = new google.maps.Marker({
          position: latlng, 
          map: map,
          title: title
        });

        google.maps.event.addListener( marker, 'click', function(){
          infoWindow.setContent('<h3>' + title + '</h3>');
          infoWindow.open(map, this);
        });

        markersArray.push(marker);

     });
    });

It's necessary to request the tweets from the server if you would like to visualize real-time data for example:

    $url = [your_url];
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); //maneja los 301, etc.
    $station_array = curl_exec( $ch );

    echo $station_array;

From this code you would be able to visualize maps with real-time data, clusters and other data-mining techniques.

To view the example of this code go to http://marcpous.com/twitter_mwc/tweetsMWC.php