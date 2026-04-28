<?php
/**
 * PHP 8.2 Compatible API Tester (New Install)
 * Fixes: CURLFile migration and object property safety.
 */

$apikey = "daMEs26rufAqasw2pUYU";

// Input Data
$post = [
    "apikey"     => $apikey,
    'method'     => "uploadNewInstall", 
    "data"       => "{\"address\":\"Brooklyntest\",\"state\":\"New Yorktest\",\"zip\":\"11215test\",\"phone\":\"718-624-5271test\",\"principal\":\"Rosa Amatotest\",\"principalemail\":\"RAmato3@schools.nyc.govtest\",\"contact\":\"Arthur Mattiatest\",\"contactemail\":\"amattia@schools.nyc.govtest\",\"contactphone\":\"718-624-527111111\",\"code\":\"75-K-372\",\"installid\":\"10943\",\"status\":\"success\",\"installdata\":{\"adultpadA\":{\"expirydate\":\"\",\"newdate\":\"2032-01-29\",\"lot\":\"123456\"},\"adultpadB\":{\"expirydate\":\"\",\"newdate\":\"2029-01-29\",\"lot\":\"123456\"},\"pediatricpad\":{\"expirydate\":\"\",\"newdate\":\"\",\"lot\":\"\"},\"comments\":\"test\",\"spare_battery_install_date\":\"2035-01-29\",\"statusindicator\":\"yes\",\"fastresponsekit\":\"yes\",\"datacardstatus\":\"yes\",\"serialnumer\":\"test\",\"pedraitickey\":\"yes\",\"physicallocation\":\"Main Entrancetest\",\"total_aeds\":\"\",\"total_responders\":\"\"}}",
    "date"       => "20160229",
    "time"       => "141406",
    "uploader"   => "tapan",
    "name"       => "tesdt",
    "esr_name"   => "sherry",
    "schoolname" => "The Childrens School PS372@834",
];

// --- FILE HANDLING (The Modern PHP 8.2 Way) ---

// 1. Signature 1
$tempfile1 = "/tmp/" . time() . "_1.png";
shell_exec("cp '../signatures/20160128_142512_0.9850373465743341.png' $tempfile1");
if (file_exists($tempfile1)) {
    // PHP 8.2 MUST use CURLFile instead of the "@" prefix
    $post["media_file"] = new CURLFile(realpath($tempfile1), 'image/png', 'media_file.png');
}

// 2. Signature 2 (ESR)
$tempfile2 = "/tmp/" . (time() + 1) . "_2.png"; // Added +1 to avoid same-second collisions
shell_exec("cp '../signatures/20160128_142512_0.9850373465743341.png' $tempfile2");
if (file_exists($tempfile2)) {
    $post["media_file_esr"] = new CURLFile(realpath($tempfile2), 'image/png', 'media_file_esr.png');
}

// --- CURL EXECUTION ---

// Ensure constants are defined or use fallback
$api_url = 'http://' . (defined('SUB_DOE') ? SUB_DOE : 'doe') . '.' . (defined('URL_WITHOUT_SUBDOMAIN') ? URL_WITHOUT_SUBDOMAIN : 'emergencyskills.com') . '/api/api.php';

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // CURLFile works natively with this

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    echo "Raw Response: " . htmlspecialchars((string)$response) . "<br><br>";
}

curl_close($ch);

// --- OUTPUT HANDLING ---

$value = json_decode((string)$response);

// Use null coalescing (??) to prevent warnings if the response isn't formatted as expected
echo "<font color='red'>";
echo "Status: " . htmlspecialchars($value->status ?? 'unknown') . "<br>";
echo "Error Message: " . htmlspecialchars($value->error_message ?? 'none') . "<br>";
echo "User ID: " . htmlspecialchars($value->userid ?? 'N/A') . "<br>";
echo "New ID: " . htmlspecialchars($value->newid ?? 'N/A') . "<br>";
echo "Attributes: <pre>" . print_r($value->attrs ?? [], true) . "</pre>";
echo "</font>";
?>