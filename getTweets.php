<?php

/*
	* https://dev.twitter.com/docs/api/1/get/search

	* Query for tweets doing checking to 4sq service 20 miles from the center of Barcelona
	* Store the tweets in a JSON file to analyze them later
*/

 $q = "http://search.twitter.com/search.json?q=4sq.com&include_entities=true&result_type=recent&geocode=41.38615876984,2.1710175275803,20mi";

 print_r($q);
 echo "<hr>";

 try {
   // Make call with cURL
   $session = curl_init($q);
   curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
   $json = curl_exec($session);

   print_r($json);

   } catch (Exception $e) {
	print_r($e);
   }

   $microtime = date("Ymdhis");
   $myFile = "files/tweetsMWC-".$microtime.".json";
   $fh = fopen($myFile, 'w') or die("can't open file");
   fwrite($fh, $json);
   fclose($fh);

?>
