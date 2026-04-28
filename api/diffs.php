<?php
/**
 * PHP 8.2 COMPATIBLE VERSION
 * This version includes a fix for the "O'Rourke" control character error
 * found in your "didn't work" logs.
 */

// Your input data
$raw_json = "{\"id\":\"1927\",\"address\":\"8101 15 Avenue\",\"city\":\"Brooklyn\",\"state\":\"NY\",\"zip\":\"11228\",\"phone\":\"718-236-2906\",\"principal\":\"Nancy Tomasuolo\",\"principalemail\":\"NTomasu@schools.nyc.gov\",\"contact\":\"Michele Ferraro\",\"contactemail\":\"mferrar8@schools.nyc.gov\",\"contactphone\":\"718-236-2906\",\"respondername1\":\"maria dituri\\n\",\"respondername2\":\"maria leibowitz\",\"respondername3\":\"\",\"respondername4\":\"\",\"respondername5\":\"\",\"respondername6\":\"\",\"responderschool1\":\"PS204\\n\",\"responderschool2\":\"PS ²t\",\"responderschool3\":\"\",\"responderschool4\":\"\",\"responderschool5\":\"\",\"responderschool6\":\"\",\"isdrillfailed\":\"no\",\"faileddrill\":\"\",\"code\":\"20-K-204\",\"Other_school_participating\":\"\",\"drillid\":\"37185\",\"status\":\"success\",\"drillinfo\":{\"stepsdata\":[{\"stepnumber\":\"1\",\"points\":\"2\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"2\",\"points\":\"1\",\"time\":\"0:07\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"3\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"4\",\"points\":\"2\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"5\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"6\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"7\",\"points\":\"3\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"8\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"9\",\"points\":\"2\",\"time\":\"0:20\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"10\",\"points\":\"3\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"11\",\"points\":\"0\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"12\",\"points\":\"1\",\"time\":\"1:22\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"13\",\"points\":\"3\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"14\",\"points\":\"0\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"}],\"totalpoints\":\"21\",\"totaltime\":\"1:58\",\"number_of_responders\":\"2\",\"number_of_aed_responding\":\"2\"}}";

// 1. Convert Encoding (Replacing deprecated utf8_encode)
// This ensures special characters like the 'squared' symbol in "PS ²" don't break.
$a = mb_convert_encoding($raw_json, 'UTF-8', 'ISO-8859-1');

// 2. Strip slashes from the source
$a = stripslashes($a);

// 3. CLEANING STEP (Solves the "Didn't Work" log issue)
// This removes hidden control characters (0-31 ASCII) that break JSON
$a = preg_replace('/[\x00-\x1F\x7F]/u', '', $a);

// 4. Decode the JSON
$data = json_decode($a);

// 5. Output
echo "<strong>JSON Status:</strong> " . json_last_error_msg() . "<br>";

echo "<pre>";
if ($data) {
    print_r($data);
} else {
    echo "Decoding failed. Raw string: " . htmlspecialchars($a);
}
echo "</pre>";

exit;
?>