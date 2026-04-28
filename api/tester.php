<?php
// Initialize variables safely
$username = $username;
$password = $password;
$getschools = $getschools;
$downloaddata = $downloaddata;
$userid = $userid;
$zipcode = $zipcode;
$schoolid = $schoolid;
$dologin = $dologin;

$apikey = "daMEs26rufAqasw2pUYU";

// Define URL constants if not already defined
if (!defined('SUB_DOE')) {
    define('SUB_DOE', 'doe'); // Sanjoy Dey
}
if (!defined('URL_WITHOUT_SUBDOMAIN')) {
    define('URL_WITHOUT_SUBDOMAIN', 'emergencyskills.com'); // Sanjoy Dey
}

/**
 * Make API call using cURL
 * PHP will automatically close the cURL handle when the function returns
 */
function makeApiCall(array $postData): array
{
    $url = 'http://' . SUB_DOE . '.' . URL_WITHOUT_SUBDOMAIN . '/api/api.php';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // No need for curl_close() - PHP handles cleanup automatically
    // curl_close($ch);
    
    return [
        'response' => $response,
        'error' => $error,
        'errno' => $errno,
        'http_code' => $httpCode,
        'success' => empty($error) && $httpCode >= 200 && $httpCode < 300
    ];
}

/**
 * Handle JSON response with error checking
 */
function handleJsonResponse(string $response): array
{
    $value = json_decode($response);
    $err = json_last_error();
    
    return [
        'data' => $value,
        'error' => $err,
        'error_message' => $err !== JSON_ERROR_NONE ? json_last_error_msg() : null
    ];
}

/**
 * Display JSON error message
 */
function displayJsonError(int $err): string
{
    return match($err) {
        JSON_ERROR_NONE => 'No errors',
        JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
        JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        default => 'Unknown error',
    };
}

// Handle login form submission
if ($dologin && $username) {
    $post = [
        'method' => "doLogin", 
        'apikey' => $apikey, 
        'username' => $username,
        'password' => $password,
    ];
    
    $apiResult = makeApiCall($post);
    
    if (!$apiResult['success']) {
        echo "<font color='red'>API Error: " . htmlspecialchars($apiResult['error']) . " (HTTP " . $apiResult['http_code'] . ")</font><br>";
    } else {
        $jsonResult = handleJsonResponse($apiResult['response']);
        
        if ($jsonResult['error'] !== JSON_ERROR_NONE) {
            echo "<font color='red'>JSON Error: " . displayJsonError($jsonResult['error']) . "</font><br>";
            echo "Raw response: " . htmlspecialchars($apiResult['response']) . "<br>";
        } elseif ($jsonResult['data']) {
            $value = $jsonResult['data'];
            echo "<font color='red'>Status: " . htmlspecialchars($value->status ?? 'Unknown') . "<br>";
            echo "Error Message: " . htmlspecialchars($value->error_message ?? 'None') . "<br>";
            echo "User ID: " . htmlspecialchars($value->userid ?? 'None') . "<br>";
            echo "Attributes: " . htmlspecialchars(print_r($value->attrs ?? null, true)) . "<br></font>";
        }
    }
}

// Handle get schools form submission
if ($getschools && $userid) {
    $post = [
        'method' => "getSchools", 
        'apikey' => $apikey, 
        'userid' => $userid,
        'zipcode' => $zipcode,
    ];
    
    $apiResult = makeApiCall($post);
    
    if (!$apiResult['success']) {
        echo "<font color='red'>API Error: " . htmlspecialchars($apiResult['error']) . " (HTTP " . $apiResult['http_code'] . ")</font><br>";
    } else {
        $jsonResult = handleJsonResponse($apiResult['response']);
        
        echo "<textarea cols='100' rows='40'>";
        if ($jsonResult['error'] !== JSON_ERROR_NONE) {
            echo "JSON Error: " . displayJsonError($jsonResult['error']) . "\n";
            echo "Raw response: " . htmlspecialchars($apiResult['response']) . "\n";
        } elseif ($jsonResult['data']) {
            $value = $jsonResult['data'];
            echo "Status: " . ($value->status ?? 'Unknown') . "\n";
            echo "Values: " . print_r($value, true) . "\n";
        } else {
            echo "No data returned\n";
        }
        echo "</textarea>";
    }
}

// Handle download data form submission
if ($downloaddata && $schoolid) {
    $post = [
        'apikey' => $apikey,
        'method' => "downloadData", 
        'schoolid' => $schoolid
    ];
    
    $apiResult = makeApiCall($post);
    
    if (!$apiResult['success']) {
        echo "<font color='red'>API Error: " . htmlspecialchars($apiResult['error']) . " (HTTP " . $apiResult['http_code'] . ")</font><br>";
    } else {
        $jsonResult = handleJsonResponse($apiResult['response']);
        
        echo "<textarea cols='100' rows='40'>";
        if ($jsonResult['error'] !== JSON_ERROR_NONE) {
            echo "JSON Error: " . displayJsonError($jsonResult['error']) . "\n";
            echo "Raw response: " . htmlspecialchars($apiResult['response']) . "\n";
        } elseif ($jsonResult['data']) {
            $value = $jsonResult['data'];
            echo "Status: " . ($value->status ?? 'Unknown') . "\n";
            echo "Values: " . print_r($value, true) . "\n";
        } else {
            echo "No data returned\n";
        }
        echo "</textarea>";
    }
}
?>
<form method='post'>
<b>Do Login</b><br>
Username: <input type='text' name='username'><br>
Password: <input type='password' name='password'><br>
<input type='submit' name='dologin' value='Login'>
<br><br>
</form>

<form method='post'>
<b>Get Schools</b><br>
User ID: <input type='number' name='userid'><br>
Zip Code: <input type='text' name='zipcode' pattern="[0-9]{5}" title="5-digit zip code"><br>
<input type='submit' name='getschools' value='Get Schools'>
<br><br>
</form>

<form method='post'>
<b>Download Data</b><br>
School ID: <input type='number' name='schoolid'><br>
<input type='submit' name='downloaddata' value='Get Data'>
<br><br>
</form>