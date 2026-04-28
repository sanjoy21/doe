<?php
/**
 * PHP 8.2 Compatible API Tester (Service Call)
 * Fixes: CURLFile class for uploads and type-safe property access.
 */

$apikey = "daMEs26rufAqasw2pUYU";

$post = [
    'method' => "uploadServiceCall", 
    'apikey' => $apikey, 
    "data"   => "{\"id\":\"13692\",\"address\":\"206 East 118th Street\",\"city\":\"New York\",\"state\":\"New York\",\"zip\":\"10035\",\"phone\":\"212-244-1274\",\"principal\":\"Marie Polinsky\",\"principalemail\":\"mpolins@schools.nyc.gov\",\"contact\":\"Robert Rosenwald\",\"contactemail\":\"rrosenwald@schools.nyc.gov\",\"contactphone\":\"(212) 860-8170\",\"code\":\"79-Q-950\",\"servicecallid\":\"11778\",\"status\":\"success\",\"servicedata\":[{\"adultpadA\":{\"expirationdate\":\"2018-03-01\",\"lot\":\"\",\"newdate\":\"\"},\"adultpadB\":{\"expirationdate\":\"2018-03-01\",\"lot\":\"\",\"newdate\":\"\"},\"pediatric\":{\"expirationdate\":\"0000-00-00\",\"lot\":\"\",\"newdate\":\"\"},\"has_frx_pediatric_key\":\"yes\",\"spare_battery_before_date\":\"2019-07-01\",\"aedid\":\"27459\",\"spare_battery_new_date\":\"\",\"PSAL_AED_out_with_coach\":\"no\",\"request_doe_send_frx\":\"no\",\"request_doe_send_fast_response_kit\":\"no\",\"has_fast_response_kit\":\"yes\",\"comments\":\"Successful 6\\/9\\/2015 12:30 PM lillie s \",\"serial_number\":\"\",\"senddatacardstatus\":\"no\",\"hasdatacardstatus\":\"no\",\"status_indicator\":\"yes\",\"sendwallcabinet\":\"no\",\"haswallcabinet\":\"yes\",\"unit_unavailable\":\"no\",\"physicallocation\":\"Multi-Purpose Classroom\",\"error_with_unit\":\"no\",\"request_doe_send_spare_battery\":\"no\",\"error_info\":\"\"}]}",
    "date"   => "20160523",
    "time"   => "105324",
    "name"   => "Robert Rosenwald",
    "esr_name"   => "Damian Carrera",
    "uploader"   => "dwcarrera@yahoo.com",
    "version"    => "2.22",
    "schoolname" => "Pathways to Graduation @ Youth Action and Homes Program"
];

// --- MODERN FILE UPLOAD HANDLING ---

// Signature 1
$file1 = "/tmp/sign_" . time() . "_1.png";
if (@copy('../signatures/20160128_142512_0.9850373465743341.png', $file1)) {
    // PHP 8.2 MUST use CURLFile
    $post["media_file"] = new CURLFile(realpath($file1), 'image/png', 'signature.png');
}

// ESR Signature
$file2 = "/tmp/sign_" . time() . "_2.png";
if (@copy('../signatures/20160128_142512_0.9850373465743341.png', $file2)) {
    $post["media_file_esr"] = new CURLFile(realpath($file2), 'image/png', 'signature_esr.png');
}

// --- CURL EXECUTION ---

// Handle URL constants safely
$sub_doe = defined('SUB_DOE') ? SUB_DOE : 'doe'; // Sanjoy Dey
$base_url = defined('URL_WITHOUT_SUBDOMAIN') ? URL_WITHOUT_SUBDOMAIN : 'emergencyskills.com'; // Sanjoy Dey
$url = "http://{$sub_doe}.{$base_url}/api/api.php";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}

curl_close($ch);

// --- RESPONSE HANDLING ---

$value = json_decode((string)$response);

// Display results using red font as requested, with safety checks for PHP 8.2
echo "<font color='red'>";
if (isset($error_msg)) {
    echo "CURL Error: " . htmlspecialchars($error_msg) . "<br>";
}

echo "Status: " . htmlspecialchars($value->status ?? 'Unknown') . "<br>";
echo "Error Message: " . htmlspecialchars($value->error_message ?? 'None') . "<br>";
echo "User ID: " . htmlspecialchars($value->userid ?? 'N/A') . "<br>";
echo "New ID: " . htmlspecialchars($value->newid ?? 'N/A') . "<br>";

// Safely print attributes
$attrs = property_exists($value, 'attrs') ? print_r($value->attrs, true) : 'None';
echo "Attributes: <pre>" . htmlspecialchars($attrs) . "</pre>";
echo "</font>";
?>