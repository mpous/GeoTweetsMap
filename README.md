GeoTweetsMap
============

Geolocate tweets from specific location with Hadoop, Hive and GoogleMaps. This source is the result of doing fast prototyping to capture the MWC 2012 tweets in Barcelona with a 4square link inside.

To run the code you would need a PHP server with Hadoop and Hive working. It's not necessary to have PHP and Hadoop connected because in this fast prototyping example I will push JSON files into the Hadoop system.

First of all, update the getTweets.php file to get the tweets from the location that you would preffer. The query to Twitter is simple:

```php
$q = "http://search.twitter.com/search.json?q=4sq.com&include_entities=true&result_type=recent&geocode=41.38615876984,2.1710175275803,20mi";
```

Afterthat, it's necessary to implement a cron to call the file getTweets.php every 5 minutes:

```
*/5 * * * * curl [your_url]/getTweets.php
```

Every 5 minutes we will receive a sample of the tweets with 4square check-ins done next to Barcelona. We will store this data in a JSON file at the files folder. An example of the JSON received from Twitter:

```
{"completed_in":0.062,...
"results":[{"created_at":"Thu, 01 Mar 2012 22:59:27 +0000","entities":{"hashtags":[],"urls":[{"url":"http:\/\/t.co\/4BHQY06y","expanded_url":"http:\/\/4sq.com\/yOQZVY","display_url":"4sq.com\/yOQZVY","indices":[48,68]}],"user_mentions":[]},"from_user":"tresact","from_user_id":284515501,"from_user_id_str":"284515501","from_user_name":"tresa carn\u00e9 torrent","geo":null,"location":"Barcelona","id":175354590907203584,"id_str":"175354590907203584","iso_language_code":"en","metadata":{"result_type":"recent"},"profile_image_url":"http:\/\/a0.twimg.com\/profile_images\/1769039754\/IMG_2252_normal.JPG","profile_image_url_https":"https:\/\/si0.twimg.com\/profile_images\/1769039754\/IMG_2252_normal.JPG","source":"&lt;a href=&quot;http:\/\/foursquare.com&quot; rel=&quot;nofollow&quot;&gt;foursquare&lt;\/a&gt;","text":"I'm at Barrio De Gracia (Barcelona) w\/ 2 others http:\/\/t.co\/4BHQY06y","to_user":null,"to_user_id":null,"to_user_id_str":null,"to_user_name":null},{"created_at":"Thu, 01 Mar 2012 22:58:58 +0000","entities":{"hashtags":[],"urls":[{"url":"http:\/\/t.co\/qQrcppvY","expanded_url":"http:\/\/4sq.com\/xBoWcg","display_url":"4sq.com\/xBoWcg","indices":[55,75]}],"user_mentions":[]},"from_user":"letibop","from_user_id":15038267,"from_user_id_str":"15038267","from_user_name":"Leti Rodr\u00edguez","geo":null,"location":"Carrer Ram\u00f3n y Cajal 80, Barce","id":175354469582774272,"id_str":"175354469582774272","iso_language_code":"pt","metadata":{"result_type":"recent"},"profile_image_url":"http:\/\/a0.twimg.com\/profile_images\/1377214161\/leti_normal.jpg","profile_image_url_https":"https:\/\/si0.twimg.com\/profile_images\/1377214161\/leti_normal.jpg","source":"&lt;a href=&quot;http:\/\/foursquare.com&quot; rel=&quot;nofollow&quot;&gt;foursquare&lt;\/a&gt;","text":"I'm at Heliogabal (Carrer Ram\u00f3n y Cajal 80, Barcelona) http:\/\/t.co\/qQrcppvY","to_user":null,"to_user_id":null,"to_user_id_str":null,"to_user_name":null},{ 
...
```

After gathering all the information, we will use Hadoop and Hive to process the information and visualize it in a map. At this point I expect that your Hadoop and Hive is working properly. 

I used Hive in order to experiment with Hadoop using the SQL-like language instead of map-reduce.

```
>hive -S -e "select get_json_object(msg, '$.results.geo.coordinates'), get_json_object(msg, '$.results.created_at[0]') from tweets" > result
```
or

```
>hive -S -e "select get_json_object(msg, '$.results.geo.coordinates'), get_json_object(msg, '$.results.location'), get_json_object(msg, '$.results.created_at[0]') from tweets" > result
```

This hive command returns us (e.g.):

```
...
[41.3343,2.05,41.3828,2.1673,41.3733,2.1518,41.3826,2.1635,41.3967,2.1747,41.5512,2.2475]       
["Rambla de Canaletes, Barcelona","Rambla de Canaletes, Barcelona","Rambla de Canaletes, Barcelona","Barcelona","Avinguda Diagonal, 545 - CC L'","Rambla de Canaletes, Barcelona","Passeig de la Vall d'Hebron, 1","Barcelona","Jordi Girona, 29, Barcelona"] 
Mon, 27 Feb 2012 12:44:14 +0000
...
```

Afterthat we are able to visualize these information in a map to understand the tweets with 4sq checkins at Barcelona during the days of an important event such as Mobile World Congress 2012 (MWC 2012).

```
> drawHeatmapResult.php > markers
```

The markers file will contain these information:

```
...
<marker location="Rambla de Canaletes, Barcelona" title="Mon, 27 Feb 2012 12:04:12 +0000" lat="41.4706" lng="2.0837" />
....
```

With these data we are able to visualize it easily with Google Maps. The mapping file should need this Javascript code:

```javascript
function loadMarkers() {
    $.get('tweetsFromHadoop.php', function(data){
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
```

It's necessary to request the tweets from the server if you would like to visualize real-time data for example:

```php
    $url = [your_url];
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true ); //maneja los 301, etc.
    $station_array = curl_exec( $ch );

    echo $station_array;
```

From this code you would be able to visualize maps with real-time data, clusters and other data-mining techniques.

To view the example of this code go to http://marcpous.com/twitter_mwc/tweetsMWC.php