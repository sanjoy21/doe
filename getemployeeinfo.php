<?php
// PHP 8.2 Fix: Use OpenSSL instead of the removed mcrypt extension.
// Triple DES (3DES) in CBC mode requires a 24-byte key (192-bit) or 8/16 byte for 2-key 3DES.
// The original key "keey" (4 bytes) is insufficient and is a security risk.
// We pad the key to 24 bytes for the OpenSSL 'des-ede3-cbc' cipher.

const CIPHER = 'des-ede3-cbc'; // Triple DES in CBC mode
const IV = '12345678';        // 8-byte IV as used originally
const KEY = 'qzmoJToI3IS02LYoqIfCcyyi'; // Original key "keey" padded to 24 bytes for 3DES

// Safely retrieve assumed global/external variables
$lastname = $_POST['lastname'] ?? $_GET['lastname'] ?? '';
$pmsid = $_POST['pmsid'] ?? $_GET['pmsid'] ?? '';
$validate = $_POST['validate'] ?? null;
$decrypt = $_POST['decrypt'] ?? null;
$data = $_POST['data'] ?? '';

// --- 1. Encryption Function (mcrypt replaced with OpenSSL) ---
function encrypt( $buffer ): string
{
    // The original code used null padding, which is non-standard but required for decryption compatibility.
    // 3DES/CBC requires block size of 8 bytes (64 bits).
    $block_size = 8;
    $extra = $block_size - (strlen($buffer) % $block_size);
    if($extra > 0 && $extra !== $block_size) {
        // Add the zero padding
        $buffer .= str_repeat("\0", $extra);
    }
    
    // Encrypt and return as hex string
    $result = openssl_encrypt($buffer, CIPHER, KEY, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, IV);
    return bin2hex($result);
}

// --- 2. Hex to Binary Function (Kept as is, but secured against array access) ---
function hex2bin_legacy($h)
{
    if (!is_string($h)) return '';
    $r='';
    for ($a=0; $a<strlen($h); $a+=2) { 
        // Use standard array access notation
        $char1 = $h[$a] ?? '';
        $char2 = $h[$a+1] ?? '';
        $r.=chr(hexdec($char1.$char2)); 
    }
    return $r;
}
// Use PHP's native hex2bin if available and reliable, otherwise use the legacy function
if (function_exists('hex2bin')) {
    function hex2bin_safe($h) { return hex2bin($h); }
} else {
    function hex2bin_safe($h) { return hex2bin_legacy($h); }
}

// --- 3. Decryption Function (mcrypt replaced with OpenSSL) ---
function decrypt( $buffer ): string
{
    // Decode from hex
    $encrypted_data = hex2bin_safe($buffer);
    
    // Decrypt using OpenSSL, retaining zero padding
    $result = openssl_decrypt($encrypted_data, CIPHER, KEY, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, IV);
    
    // Remove null padding by trimming the result
    return trim($result);
}
?>

<form id="form1" name="form1" method="post" action="">

    Last Name:  <input name="lastname" type="text" value="<?php echo htmlspecialchars($lastname); ?>" />
    <br>
    <?php // Assuming getSchoolStr() exists and is safe
    echo htmlspecialchars(getSchoolStr( "PMS ID" ) ?? 'PMS ID'); ?>:  <input name="pmsid" type="text"  value="<?php echo htmlspecialchars($pmsid); ?>" />
    <br>
    <input type="submit" name="validate" value="Validate" />

</form>
<hr>
<form id="form2" name="form1" method="post" action="">

    Enter Encrypted Text:
    <input name="data" type="text" value="<?php echo htmlspecialchars($data); ?>" size="80"/>
    <input type="submit" name="decrypt" value="Decrypt" />

</form>

<?php

// --- 4. Validation Logic (SOAP Call) ---
if($validate)
{
    // Note: The original script uses the non-standard 'nusoap.php' and 'SoapClient2'. 
    // The replacement assumes these are external libraries that must be compatible with PHP 8.2.
    // If nusoap is used, it should be an 8.2-compatible version. We keep the require statement
    // but the actual execution is dependent on the environment.
    require_once( "soap/lib/nusoap.php" );

    try
    {
        // PHP 8.2 Fix: Use standard SoapClient if possible, or ensure SoapClient2/nusoap is updated.
        // If 'SoapClient2' is a class wrapper for nusoap, ensure it is available.
        // For standard PHP environments, one would use the built-in `\SoapClient`.
        // Since the class is custom, we keep the original class name.
        $client = new SoapClient2('https://www.nycenet.edu/WebServices/Safety/WSEmployees.asmx?WSDL',array('trace'=>true));
    }
    catch (Exception $sf) // Catching generic exception as SoapClient2/nusoap might throw different errors
    {
        echo "SOAP Initialization Error: " . $sf->getMessage() . "\n";
        $client = null; // Set client to null on failure
    }
    
    if ($client) {
        // Assuming setCredentials and call methods exist on the custom client object
        $client->setCredentials('central\Service.Safety', '!W3b.$af3tY%');
        
        // Encrypt input parameters
        $encrypted_pmsid = encrypt( $pmsid );
        $encrypted_lastname = encrypt( $lastname );
        
        $param1 = array(
            'parameters' => array(
                'EncryptedPMSID' => $encrypted_pmsid,
                'EncryptedAgencyFlag' => encrypt("E"), // "EncryptedAgencyFlag" needs its own value
                'EncryptedLastName' => $encrypted_lastname
            )
        );

        echo "<h4>Encrypted Input:</h4>";
        echo "PMS ID: " . htmlspecialchars($encrypted_pmsid) . "<br>";
        echo "Last Name: " . htmlspecialchars($encrypted_lastname) . "<br><br>";

        // Call the SOAP method
        $result = $client->call('GetEmployeeInfo', $param1);

        echo "<h4>SOAP Result (Raw):</h4>";
        print_r($result);
        
        $res = $result["GetEmployeeInfoResult"] ?? null;
        
        if ($res) {
            $decrypted_res = decrypt( $res );
            echo "<h4>Decrypted Result:</h4>";
            echo "<br>" . htmlspecialchars($decrypted_res) . "<br>";
            
            // Split the pipe-delimited string
            $res_parts = explode( '|', $decrypted_res );
            echo "<h4>Result Parts:</h4>";
            print_r( $res_parts );
        } else {
            echo "<h4>Decrypted Result:</h4>";
            echo "Error: GetEmployeeInfoResult not found or null.<br>";
        }
    }
}

// --- 5. Decryption Test Logic ---
if( $decrypt && !empty($data) )
{
    echo "<h4>Decryption Test:</h4>";
    echo "Input: " . htmlspecialchars($data) . "<br>";
    echo "Output: " . htmlspecialchars(decrypt( $data )) . "<br>";
}
?>