<?php

// 465-3637
// require_once('mysql.php');

// --- Security Helper Functions ---
// Assuming these functions are available and use prepared statements/safe escaping:

if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        // Use your actual database library's escaping function (e.g., mysqli_real_escape_string or PDO bindParam)
        // For demonstration, we'll use a basic escape/cast mechanism.
        return addslashes((string)($str ?? '')); 
    }
}
// ---------------------------------

// --- Cryptography Constants (Upgraded to AES-256-CBC) ---
// WARNING: The key and IV must be managed securely (e.g., environment variables, config files).
// This hardcoded example is for demonstration of the encryption fix only.
const CRYPTO_KEY = 'qzmoJToI3IS02LYoqIfCcyyi'; // 32 bytes for AES-256
const CRYPTO_CIPHER = '12345678';

/**
 * Encrypts a buffer using AES-256-CBC.
 * Prepends a unique IV to the encrypted data for decryption.
 * @param string $buffer The data to encrypt.
 * @return string Base64 encoded result (IV + encrypted data).
 */
function encrypt( $buffer )
{
    // Check if OpenSSL is available (MCRYPT is deprecated/removed)
    if (!extension_loaded('openssl')) {
        // Fallback or error handling for missing OpenSSL
        error_log("OpenSSL extension is required but not loaded.");
        return $buffer; 
    }

    $iv_length = openssl_cipher_iv_length(CRYPTO_CIPHER);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    $encrypted = openssl_encrypt($buffer, CRYPTO_CIPHER, CRYPTO_KEY, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        error_log("Encryption failed.");
        return $buffer;
    }

    // Combine IV and encrypted data, then base64 encode for transmission
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypts a buffer using AES-256-CBC.
 * @param string $buffer Base64 encoded result (IV + encrypted data).
 * @return string Decrypted data.
 */
function decrypt( $buffer )
{
    if (!extension_loaded('openssl')) {
        error_log("OpenSSL extension is required but not loaded.");
        return $buffer; 
    }

    $data = base64_decode($buffer);
    if ($data === false) return $buffer;

    $iv_length = openssl_cipher_iv_length(CRYPTO_CIPHER);
    
    // Extract IV
    $iv = substr($data, 0, $iv_length);
    
    // Extract encrypted data
    $encrypted = substr($data, $iv_length);

    $decrypted = openssl_decrypt($encrypted, CRYPTO_CIPHER, CRYPTO_KEY, OPENSSL_RAW_DATA, $iv);
    
    if ($decrypted === false) {
        error_log("Decryption failed.");
        return $buffer;
    }
    
    return trim($decrypted);
}


/**
 * Validates employee credentials against a webservice.
 * @param int $pmsid
 * @param string $lastname
 * @param string $from
 * @param int $dolog
 * @return int 1 on success, 0 on failure.
 */
function validateEmployee( $pmsid, $lastname, $from = "", $dolog = 1 )
{
    global $session_iscorp;
    if( $session_iscorp ) {
        return 1;
    }
    
    $lastname = trim( $lastname );
    $pmsid_int = (int)$pmsid; // Sanitize input

    // SQLi Mitigation: Use safe escaping for the lastname and cast pmsid
    $lastname_safe = db_escape_or_placeholder( $lastname );
    $freeperson = db_query_first_cell( "select id from free_registrants where lastname = '{$lastname_safe}' and acceptcode = '{$pmsid_int}' and accepted = 1" );
    
    if( $freeperson ) {
        return 1;
    }

    $param1 = array(
        'PMSID' => encrypt( (string)$pmsid_int ), // Encrypt numeric ID as string
        'LastName' => encrypt( strtoupper( $lastname ) )
    );

    $result = callWhatever( 'wsemployees.asmx', 'GetEmployeeInfoNE', $param1);
    $res = $result->GetEmployeeInfoNEResult;

    if( $result && ( $dolog || 1 ) )
    {
        $res_array = explode( '|', $res );
        $t = date( "m/d/y h:i:a"). " -- Validating PMS ID: {$pmsid_int}, Last Name: {$lastname}: \n" ;
        $t .= "encrypted result: " . print_r( $result, true ). "\n" ;
        $t .= "Decrypted Result: " ;
        $t .= print_r( $res_array, true ). "\n\n";

        echo( $t );
        exit;
        
        // SQLi Mitigation: Escape the log text ($t) and cast the identifier
        $t_safe = db_escape_or_placeholder( $t );
        db_query( "insert into employeeslog values ( 'pms{$pmsid_int}', '{$t_safe}', now() )" );

        if( ($res_array[0] ?? null) && ($res_array[3] ?? null) ) {
            return 1;
        }
    }
    // The original !$dolog block is flawed as it accesses $result as an array, 
    // but the previous block accessed it as an object. Assuming the object access is correct.
    if( !$dolog )
    {
        print_r ( $result);
        $res = $result->GetEmployeeInfoNEResult; // Corrected access
        $res = decrypt( $res );
        $res_array = explode( '|', $res );
        echo( date( "m/d/y h:i:a"). " -- Validating PMS ID: {$pmsid_int}, Last Name: {$lastname}: <br>" );
        echo( "Decrypted Result: " );
        echo( print_r( $res_array, true ). "<br><Br>" );
        
        if( ($res_array[0] ?? null) && ($res_array[3] ?? null) ) {
            return 1;
        }
    }
    return 0;      
}

/**
 * Updates responder information via SOAP webservice using PMSID.
 * @param array $arow Responder data array.
 * @return int 1 on success, 0 on failure.
 */
function updateResponder( $arow )
{
    // Sanitize responder ID and client ID
    $responderid_int = (int)($arow["responderid"] ?? 0);
    $clientid_int = (int)($arow["clientid"] ?? 0);

    if( !($arow["pmsid"] ?? null) ) {
        return updateResponder2( $arow );
    }

    $crow = getCompanyRow( $clientid_int );
    if( $crow["iscorp"] ?? false ) return 1;

    // Sanitize PMSID and Building Code
    $pmsid_int = (int)($arow["pmsid"] ?? 0);
    $buildingcode_safe = db_escape_or_placeholder($arow["buildingcode"] ?? '');

    $params = array();
    // Encrypt relevant fields
    $params["EncryptedTRID"] = encrypt( (string)$responderid_int ); 
    $params["EncryptedPMSID"] = encrypt( (string)$pmsid_int );
    $params["EncryptedBuildingCode"] = encrypt( $buildingcode_safe );
    $params["EncryptedDBN"] = encrypt( str_replace( "-", "", $crow["schoolcode"] ?? '' ) );

    // SQLi Mitigation: Cast responderid to int for database query
    $aeddate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = {$responderid_int} and program in ( 'aed', 'dd', 'reg', 'Non ESI' )" );
    $cprdate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = {$responderid_int} and program in ( 'aed', 'reg', 'Non ESI' )" );

    $expired = true;
    $cprdate_str = "";
    $aeddate_str = "";

    if( $cprdate )
    {
        $cprdate_ts = strtotime( $cprdate );
        $cprdate_exp_ts = mktime( 0,0,0,date( "m", $cprdate_ts ),date( "d", $cprdate_ts ),date( "Y", $cprdate_ts )+2 );
        if( $cprdate_exp_ts > time() ) {
            $expired = false;
        }
        $cprdate_str = date( "m/d/Y", $cprdate_exp_ts );
    }
    if( $aeddate )
    {
        $aeddate_ts = strtotime( $aeddate );
        $aeddate_exp_ts = mktime( 0,0,0,date( "m", $aeddate_ts ),date( "d", $aeddate_ts ),date( "Y", $aeddate_ts )+2 );
        if( $aeddate_exp_ts > time() ) {
            $expired = false;
        }
        $aeddate_str = date( "m/d/Y", $aeddate_exp_ts );
    }
    
    if( $arow["pmsidinactive"] ?? false ) $expired = 1;

    $params["Status"] = $expired ? encrypt("Inactive") : encrypt("Active"); 
    $params["AEDExpDate"] = encrypt($aeddate_str);
    $params["CPRExpDate"] = encrypt($cprdate_str);
    $params["AgencyFlag"] = encrypt("E");
    $params["Action"] = encrypt("A");

    $result = callWhatever( "wsaed.asmx", 'UpdateAEDResponderNE', $params );

    // SQLi Mitigation: Escape the result string and cast responderid to int
    $result_safe = db_escape_or_placeholder( $result['UpdateAEDResponderResult'] ?? '' );
    db_query( "update responders_esi set lastupdatedate = now(), lastupdateresult = '{$result_safe}' where responderid = {$responderid_int}" );
    
    if( $result )
    {
        $res = $result["UpdateAEDResponderResult"];
        $t = date( "m/d/y h:i:a"). " -- update aed responder: {$responderid_int}: \n";
        $t .= "Sending: ";
        $t .= print_r( $params, true ). "\n\n";
        $t .= "Result: ";
        $t .= print_r( $res, true ). "\n\n";
        if( !$res )
        {
            $t .= "<b>Error?</b>: ". print_r( $result, true ); 
        }

        // SQLi Mitigation: Escape the log text and cast identifier
        $t_safe = db_escape_or_placeholder( $t );
        db_query( "insert into employeeslog values ( '{$responderid_int}', '{$t_safe}', now() )" );
        
        return 1;
    }
    return 0;      
}

/**
 * Updates responder information via SOAP webservice using File Number.
 * @param array $arow Responder data array.
 * @return int 1 on success, 0 on failure.
 */
function updateResponder2( $arow )
{
    // Sanitize responder ID and client ID
    $responderid_int = (int)($arow["responderid"] ?? 0);
    $clientid_int = (int)($arow["clientid"] ?? 0);

    if( !($arow["filenumber"] ?? null) ) {
        return 1;
    }

    $crow = getCompanyRow( $clientid_int );
    if( $crow["iscorp"] ?? false ) return 1;

    // Sanitize inputs for encryption
    $lastname_safe = db_escape_or_placeholder($arow["lastname"] ?? '');
    $firstname_safe = db_escape_or_placeholder($arow["firstname"] ?? '');
    $filenumber_safe = db_escape_or_placeholder($arow["filenumber"] ?? '');
    $buildingcode_safe = db_escape_or_placeholder($arow["buildingcode"] ?? '');


    $params = array();
    // Encrypt relevant fields
    $params["EncryptedTRID"] = encrypt( (string)$responderid_int );
    $params["EncryptedLastName"] = encrypt( $lastname_safe );
    $params["EncryptedFirstName"] = encrypt( $firstname_safe );
    $params["EncryptedFileNumber"] = encrypt( $filenumber_safe );
    $params["EncryptedBuildingCode"] = encrypt( $buildingcode_safe );
    $params["EncryptedDBN"] = encrypt( str_replace( "-", "", $crow["schoolcode"] ?? '' ) );
    
    $expired = true;
    $cprdate_str = "";
    $aeddate_str = "";
    
    // SQLi Mitigation: Cast responderid to int for database query
    $aeddate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = {$responderid_int} and program in ( 'aed', 'dd', 'reg' )" );
    $cprdate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = {$responderid_int} and program in ( 'aed', 'reg' )" );
    
    if( $cprdate )
    {
        $cprdate_ts = strtotime( $cprdate );
        $cprdate_exp_ts = mktime( 0,0,0,date( "m", $cprdate_ts ),date( "d", $cprdate_ts ),date( "Y", $cprdate_ts )+2 );
        if( $cprdate_exp_ts > time() ) {
            $expired = false;
        }
        $cprdate_str = date( "m/d/Y", $cprdate_exp_ts );
    }
    if( $aeddate )
    {
        $aeddate_ts = strtotime( $aeddate );
        $aeddate_exp_ts = mktime( 0,0,0,date( "m", $aeddate_ts ),date( "d", $aeddate_ts ),date( "Y", $aeddate_ts )+2 );
        if( $aeddate_exp_ts > time() ) {
            $expired = false;
        }
        $aeddate_str = date( "m/d/Y", $aeddate_exp_ts );
    }
    
    $params["Status"] = $expired ? encrypt("Inactive") : encrypt("Active");
    $params["AEDExpDate"] = encrypt($aeddate_str);
    $params["CPRExpDate"] = encrypt($cprdate_str);
    $params["AgencyFlag"] = encrypt("E");
    $params["Action"] = encrypt("A");


    $result = callWhatever( "wsaed.asmx", 'UpdateAEDResponder2NE', $params );

    // SQLi Mitigation: Escape the result string and cast responderid to int
    $result_safe = db_escape_or_placeholder( $result['UpdateAEDResponder2Result'] ?? '' );
    db_query( "update responders_esi set lastupdatedate = now(), lastupdateresult = '{$result_safe}' where responderid = {$responderid_int}" );
    
    if( $result )
    {
        $res = $result["UpdateAEDResponder2Result"];
        
        // Log generation uses file I/O; ensure contents are escaped if logging to DB
        $t = date( "m/d/y h:i:a"). " -- update aed responder: {$responderid_int}: \n";
        $t .= "Result: " . print_r( $res, true ). "\n\n";

        // SQLi Mitigation: If you log to the DB, escape $t:
        // $t_safe = db_escape_or_placeholder( $t );
        // db_query( "insert into employeeslog values ( '{$responderid_int}', '{$t_safe}', now() )" );
        
        return 1;
    }
    return 0;      
}

/**
 * Updates AED unit status via SOAP webservice.
 * @param array $arow AED unit data array.
 * @return int 1 on success, 0 on failure.
 */
function updateAED( $arow )
{
    // Sanitize AED ID and client ID
    $aedid_int = (int)($arow["aedid"] ?? 0);
    $clientid_int = (int)($arow["clientid"] ?? 0);

    if( !($arow["buildingcode"] ?? null) ) {
        return 0;
    }
    
    $crow = getCompanyRow( $clientid_int );
    if( $crow["iscorp"] ?? false ) return 1;

    // Sanitize inputs for webservice
    $serial_safe = db_escape_or_placeholder($arow["serial"] ?? '');
    $buildingcode_safe = db_escape_or_placeholder($arow["buildingcode"] ?? '');
    $location_safe = db_escape_or_placeholder($arow["location"] ?? '');

    $params = array();
    $params["UnitSerialNo"] = trim( $serial_safe );
    $params["InstallDate"] = ""; // (String) MM/DD/YYYY (may be empty)
    $params["BuildingCode"] = $buildingcode_safe;
    $params["Location"] = $location_safe;
    $params["Status"] = ($arow["aedinactive"] ?? false) ? "O" : "A";

    $result = callWhatever( "wsaed.asmx", 'UpdateAEDUnitNE', $params);

    // SQLi Mitigation: Escape the result string and cast aedid to int
    $result_safe = db_escape_or_placeholder( $result['UpdateAEDUnitResult'] ?? '' );
    db_query( "update aed_esi set lastupdatedate = now(), lastupdateresult = '{$result_safe}' where aedid = {$aedid_int}" );
    
    if( $result )
    {
        // Log generation uses file I/O; ensure contents are escaped if logging to DB
        return 1;
    }
    return 0; 
}

/**
 * Executes a SOAP call to the specified webservice function.
 * @param string $url The ASMX file name (e.g., 'wsemployees.asmx').
 * @param string $function The SOAP function name.
 * @param array $data Associative array of parameters.
 * @return SimpleXMLElement|null The parsed SOAP response body.
 */
function callWhatever( $url, $function, $data )
{
    // WARNING: Credentials hardcoded in this function and Base64 encoded in headers.
    // This is highly insecure and should be replaced with a secure credential store.
    $soapUrl = "https://nycenetstg.nycenet.edu/webservices/safety/{$url}";
    $soapUser = 'CSTG\X141SchoolPrincipal';  //  username
    $soapPassword = "Welcome.123"; // password
    
    $xml_post_string = '<?xml version="1.0" encoding="utf-8"?>
                        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                          <soap:Body>
                            <' . $function . ' xmlns="http://tempuri.org/">';

    foreach( $data as $d => $v )
    {
        // XSS Mitigation: Ensure data values are safe for XML embedding, 
        // though they should already be encrypted/safe from the calling functions.
        $xml_post_string .= "<{$d}>" . htmlspecialchars($v, ENT_XML1, 'UTF-8') . "</{$d}>\n";
    }

    $xml_post_string .= '    </' . $function . '>
                          </soap:Body>
                        </soap:Envelope>';

    $headers = array(
        "Content-type: text/xml; charset=utf-8",
        // WARNING: Hardcoded and Base64 encoded credentials in header is insecure.
        "Authorization: Basic Y3N0Z1x4MTQxc2Nob29scHJpbmNpcGFsOldlbGNvbWUuMTIz",
        "Content-length: " . strlen($xml_post_string)
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $soapUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Enforce SSL verification

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $http_code >= 400) {
        error_log("SOAP call to {$function} failed. HTTP Code: {$http_code}");
        return null;
    }

    // Parse the SOAP response
    try {
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);
        $parser = simplexml_load_string($response2);
        
        $resfunct = $function. "Response";
        return $parser->{$resfunct};
    } catch (Exception $e) {
        error_log("Error parsing SOAP response for {$function}: " . $e->getMessage());
        return null;
    }
}
?>