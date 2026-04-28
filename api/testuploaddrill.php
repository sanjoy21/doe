<?php
/**
 * PHP 8.2 Compatible API Tester
 */

$apikey = "daMEs26rufAqasw2pUYU";

// Use long-form array syntax or keep short; both are fine in 8.2.
// I've cleaned up the JSON string slightly for readability.
$post = [
    'method'     => "uploadDrill",
    'apikey'     => $apikey,
    "data"       => "{\"id\":\"1882\",\"address\":\"2944 Pitkin Avenue\",\"city\":\"Brooklyn\",\"state\":\"NY\",\"zip\":\"11208\",\"phone\":\"718-647-1740\",\"principal\":\"Sharon Mahabir\",\"principalemail\":\"SMahabi@schools.nyc.gov\",\"contact\":\"Rewa Chan\",\"contactemail\":\"rchan2@schools.nyc.gov\",\"contactphone\":\"718-647-1740\",\"respondername1\":\"rewa chan\",\"respondername2\":\"Gregorio\'s Athanasakis\",\"respondername3\":\"Johnny Etienne\",\"respondername4\":\"Pamela mccomb\",\"respondername5\":\"\",\"respondername6\":\"\",\"responderschool1\":\"\",\"responderschool2\":\"\",\"responderschool3\":\"\",\"responderschool4\":\"\",\"responderschool5\":\"\",\"responderschool6\":\"\",\"isdrillfailed\":\"no\",\"faileddrill\":\"\",\"code\":\"19-K-214\",\"Other_school_participating\":\"\",\"drillid\":\"37865\",\"status\":\"success\",\"drillinfo\":{\"stepsdata\":[{\"stepnumber\":\"1\",\"points\":\"2\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"2\",\"points\":\"1\",\"time\":\"0:05\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"3\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"4\",\"points\":\"2\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"5\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"6\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"7\",\"points\":\"3\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"8\",\"points\":\"1\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"9\",\"points\":\"2\",\"time\":\"0:44\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"10\",\"points\":\"3\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"11\",\"points\":\"0\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"12\",\"points\":\"1\",\"time\":\"1:55\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"13\",\"points\":\"3\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"},{\"stepnumber\":\"14\",\"points\":\"0\",\"time\":\"\",\"comments\":\"\",\"ischecked\":\"yes\"}],\"totalpoints\":\"21\",\"totaltime\":\"7:46\",\"number_of_responders\":\"4\",\"number_of_aed_responding\":\"2\"}}",
    "date"       => "20160511",
    "time"       => "93752",
    "schoolname" => "",
    "uploader"   => "mjb12827@yahoo.com"
];

function getPostData()
{
    global $post;
    $raw_data = $post["data"] ?? '';

    // Attempt 1: Standard decode
    $data = json_decode(stripslashes($raw_data));

    // Attempt 2: Handle encoding if Attempt 1 failed
    if (json_last_error() !== JSON_ERROR_NONE) {
        // utf8_encode() is DEPRECATED in PHP 8.2. Use mb_convert_encoding.
        $converted = mb_convert_encoding($raw_data, 'UTF-8', 'ISO-8859-1');
        $data = json_decode(stripslashes($converted));
    }

    // Attempt 3: Strip non-printable/control characters if still failing
    if (json_last_error() !== JSON_ERROR_NONE) {
        // More robust regex for stripping control characters while keeping UTF-8
        $stripped = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw_data);
        $data = json_decode(stripslashes($stripped));
    }

    return $data;
}

/**
 * API URL Configuration
 * Note: Ensure SUB_DOE and URL_WITHOUT_SUBDOMAIN are defined constants 
 * elsewhere in your config.
 */
$apiUrl = 'http://' . (defined('SUB_DOE') ? SUB_DOE : 'doe') . '.' . (defined('URL_WITHOUT_SUBDOMAIN') ? URL_WITHOUT_SUBDOMAIN : 'emergencyskills.com') . '/api/api.php';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo "response: " . htmlspecialchars((string)$response) . "<br>";
}
curl_close($ch);

// Safely decode the response
$value = json_decode((string)$response);

// Use null coalescing (??) to prevent "Undefined property" errors in PHP 8.2
echo "<font color='red'>Status: " . htmlspecialchars($value->status ?? 'unknown') . "<br>";
echo "Error Message: " . htmlspecialchars($value->error_message ?? 'none') . "<br>";
echo "User ID: " . htmlspecialchars($value->userid ?? 'N/A') . "<br>";
echo "New ID: " . htmlspecialchars($value->newid ?? 'N/A') . "<br>";
echo "Attributes: <pre>" . print_r($value->attrs ?? [], true) . "</pre></font><br>";
?>