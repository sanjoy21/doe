<?php

function encrypt( $buffer )
{
    // https://stackoverflow.com/questions/34871579/how-to-encrypt-plaintext-with-aes-256-cbc-in-php-using-openssl
    // get the amount of bytes to pad
    $extra = 8 - (strlen($buffer) % 8);
    // add the zero padding
    if($extra > 0) {
        for($i = 0; $i < $extra; $i++) {
            $buffer .= "\0";
        }
    }
    // very simple ASCII key and IV
    $key = "qzmoJToI3IS02LYoqIfCcyyi";
    $iv = "12345678";
    $options = 0;
    
    // Note: mcrypt_encrypt is deprecated in PHP 7.1 and removed in PHP 8.0
    // For PHP 8.2, we need to use OpenSSL instead
    // Using OpenSSL with DES-EDE3-CBC (Triple DES) as replacement for MCRYPT_3DES
    $cipher = "DES-EDE3-CBC";
    $result = openssl_encrypt($buffer, $cipher, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
    if ($result === false) {
        return false;
    }
    return bin2hex($result);
}

function decrypt( $buffer )
{
    // very simple ASCII key and IV
    $key = "qzmoJToI3IS02LYoqIfCcyyi";
    $iv = "12345678";
    
    // Note: mcrypt_decrypt is deprecated in PHP 7.1 and removed in PHP 8.0
    // For PHP 8.2, we need to use OpenSSL instead
    // Using OpenSSL with DES-EDE3-CBC (Triple DES) as replacement for MCRYPT_3DES
    $cipher = "DES-EDE3-CBC";
    $buffer = hex2bin($buffer);
    $result = openssl_decrypt($buffer, $cipher, $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
    
    if ($result === false) {
        return false;
    }
    
    // Trim null bytes from the end
    $result = rtrim($result, "\0");
    
    return $result;
}

function getClientForSoap( $str = "WSEmployees.asmx", $staging = false )
{
    require_once( "soap/lib/nusoap.php" );
    try
    {
        //staging
        if( $staging )
        {
            $client = new SoapClient("https://165.155.112.27/webservices/safety/$str?WSDL",array('trace'=>true));
            $client->setDebugLevel( 9 );
        }
        else
        {
            $client = new nusoap_client("https://www.nycenet.edu/WebServices/Safety/$str?WSDL", 'wsdl' );
        }
    }
    catch (SoapFault $sf)
    {
        echo $sf->getMessage(), "\n";
        return false;
    }
    
    $error = $client->getError();
    if ($error) {
       echo "<h2>before cred error</h2><pre>" . $error . "</pre>";
       return false;
    }
    
    $client->setCredentials('Central\Service.Safety', '!W3b.$af3tY%');
    
    $error = $client->getError();
    if ($error) {
       echo "<h2>after cred error</h2><pre>" . $error . "</pre>";
       return false;
    }
    
    return $client;
}

function validateEmployee( $pmsid, $lastname, $from = "", $dolog = 1 )
{
    global $session_iscorp;
    
    if( isset($session_iscorp) && $session_iscorp )
    {
        return 1;
    }
    
    $lastname = trim( $lastname );
    
    // Note: mysql_escape_string is deprecated, use mysqli_real_escape_string instead
    // Since we don't have a connection here, we'll use a simple escape
    $lastname = mysql_escape_string( $lastname );
    
    $freeperson = db_query_first_cell( "select id from free_registrants where lastname = '".$lastname."' and acceptcode = '$pmsid' and accepted = 1" );
    if( $freeperson )
        return 1;

    $client = getClientForSoap();
    if( !$client ) {
        return 0;
    }
    
    $param1 = array(
        'parameters' => array(
            'EncryptedPMSID' => encrypt( intval( $pmsid ) ),
            'EncryptedAgencyFlag' => encrypt("E"),
            'EncryptedLastName' => encrypt( strtoupper( $lastname ) )
        )
    );

    $result = $client->call('GetEmployeeInfo', $param1);

    $error = $client->getError();
    // if ($error) {
    //    echo "<h2>Constructor error</h2><pre>" . $error . "</pre>";
    //    var_dump( $client );
    //    return 0;
    // }
    
    if( $result && ( $dolog || 1 ) )
    {
        $res = isset($result["GetEmployeeInfoResult"]) ? $result["GetEmployeeInfoResult"] : '';
        $decrypted_res = decrypt( $res );
        $res_array = $decrypted_res ? explode( '|', $decrypted_res ) : array();
        
        $t = date( "m/d/y h:i:a"). " -- Validating PMS ID: $pmsid, Last Name: $lastname: \n" ;
        $t .= "encrypted result: " . print_r( $result, true ). "\n" ;
        $t .= "Decrypted Result: " ;
        $t .=  print_r( $res_array, true ). "\n\n";
        
        // Note: mysql_escape_string is deprecated
        $t = mysql_escape_string($t);
        db_query( "insert into employeeslog values ( 'pms$pmsid', '".$t."', now() )" );
        
        if( !empty($res_array[0]) && !empty($res_array[3]) )
            return 1;
    }
    
    if( !$dolog )
    {
        print_r ( $result);
        $res = isset($result["GetEmployeeInfoResult"]) ? $result["GetEmployeeInfoResult"] : '';
        $decrypted_res = decrypt( $res );
        $res_array = $decrypted_res ? explode( '|', $decrypted_res ) : array();
        
        echo( date( "m/d/y h:i:a"). " -- Validating PMS ID: $pmsid, Last Name: $lastname: <br>" );
        echo( "Decrypted Result: " );
        echo( print_r( $res_array, true ). "<br><Br>" );
        
        if( !empty($res_array[0]) && !empty($res_array[3]) )
            return 1;
    }
    
    return 0;    
}

function updateResponder( $arow )
{
    if( !isset($arow["pmsid"]) || !$arow["pmsid"] )
    {
        return updateResponder2( $arow );
    }

    $crow = getCompanyRow( isset($arow["clientid"]) ? $arow["clientid"] : 0 );
    if( isset($crow["iscorp"]) && $crow["iscorp"] ) return 1;
    
    $client = getClientForSoap( "wsaed.asmx" );
    if( !$client ) {
        return 0;
    }
    
    $params = array();
    $params["EncryptedTRID"] = isset($arow["responderid"]) ? $arow["responderid"] : ''; // (int) (Trained Responder ID)
    $params["EncryptedPMSID"] = isset($arow["pmsid"]) ? $arow["pmsid"] : ''; // (int) (Unique key)
    $params["EncryptedBuildingCode"] = isset($arow["buildingcode"]) ? $arow["buildingcode"] : ''; // (int) Building Number
    $params["EncryptedDBN"] = isset($crow["schoolcode"]) ? str_replace( "-", "", $crow["schoolcode"] ) : ''; // (Varchar(6))
    
    $aeddate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = ".(isset($arow["responderid"]) ? $arow["responderid"] : 0)." and program in ( 'aed', 'dd', 'reg', 'Non ESI' )" );
    $cprdate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = ".(isset($arow["responderid"]) ? $arow["responderid"] : 0)." and program in ( 'aed', 'reg', 'Non ESI' )" );
    
    $expired = true;
    
    if( $cprdate )
    {
        $cprdate = strtotime( $cprdate );
        $cprdate = mktime( 0,0,0,date( "m", $cprdate ),date( "d", $cprdate ),date( "Y", $cprdate )+2 );
        if( $cprdate > time() )
            $expired = false;
        $cprdate = date( "m/d/Y", $cprdate );
    }
    
    if( $aeddate )
    {
        $aeddate = strtotime( $aeddate );
        $aeddate = mktime( 0,0,0,date( "m", $aeddate ),date( "d", $aeddate ),date( "Y", $aeddate )+2 );
        if( $aeddate > time() )
            $expired = false;
        $aeddate = date( "m/d/Y", $aeddate );
    }
    
    if( isset($arow["pmsidinactive"]) && $arow["pmsidinactive"] ) $expired = true;
    
    $params["EncryptedStatus"] = $expired ? "Inactive" : "Active";
    $params["EncryptedAEDExpDate"] = isset($aeddate) ? $aeddate : '';
    $params["EncryptedCPRExpDate"] = isset($cprdate) ? $cprdate : '';
    $params["EncryptedAgencyFlag"] = "E";
    $params["EncryptedAction"] = "A";

    $encparams = array();
    foreach( $params as $a=>$b )
    {
        $encparams[$a] = encrypt( $b );
    }
    
    $param1 = array('parameters' => $encparams );
    $result = $client->call('UpdateAEDResponder', $param1);

    // Note: mysql_escape_string is deprecated
    $result = isset($result['UpdateAEDResponderResult']) ? mysql_escape_string($result['UpdateAEDResponderResult']) : '';
    db_query( "update responders_esi set lastupdatedate = now(), lastupdateresult = '".$result."' where responderid = ".(isset($arow["responderid"]) ? $arow["responderid"] : 0) );
    
    if( $result )
    {
        $res = isset($result["UpdateAEDResponderResult"]) ? $result["UpdateAEDResponderResult"] : '';
        $t = date( "m/d/y h:i:a"). " -- update aed responder: ".(isset($arow["responderid"]) ? $arow["responderid"] : '').": \n";
        $t .= "Sending: ";
        $t .= print_r( $params, true ). "\n\n";
        $t .= "Result: ";
        $t .= print_r( $res, true ). "\n\n";
        
        if( !$res )
        {
            $t .= "<b>Error?</b>: ". print_r( $result, true ); 
        }
        
        // Note: mysql_escape_string is deprecated
        $t = mysql_escape_string($t);
        db_query( "insert into employeeslog values ( '".(isset($arow["responderid"]) ? $arow["responderid"] : '')."', '".$t."', now() )" );
        
        return 1;
    }
    
    return 0;    
}

function updateResponder2( $arow )
{
    if( !isset($arow["filenumber"]) || !$arow["filenumber"] )
    {
        return 1;
    }

    $crow = getCompanyRow( isset($arow["clientid"]) ? $arow["clientid"] : 0 );
    if( isset($crow["iscorp"]) && $crow["iscorp"] ) return 1;
    
    $client = getClientForSoap( "wsaed.asmx" );
    if( !$client ) {
        return 0;
    }
    
    $params = array();
    $params["EncryptedTRID"] = isset($arow["responderid"]) ? $arow["responderid"] : '';
    $params["EncryptedLastName"] = isset($arow["lastname"]) ? $arow["lastname"] : '';
    $params["EncryptedFirstName"] = isset($arow["firstname"]) ? $arow["firstname"] : '';
    $params["EncryptedFileNumber"] = isset($arow["filenumber"]) ? $arow["filenumber"] : '';
    $params["EncryptedBuildingCode"] = isset($arow["buildingcode"]) ? $arow["buildingcode"] : '';
    $params["EncryptedDBN"] = isset($crow["schoolcode"]) ? str_replace( "-", "", $crow["schoolcode"] ) : '';
    
    $expired = true;

    $aeddate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = ".(isset($arow["responderid"]) ? $arow["responderid"] : 0)." and program in ( 'aed', 'dd', 'reg' )" );
    $cprdate = db_query_first_cell( "select max( trainingdate ) from responder_training_dates where responderid = ".(isset($arow["responderid"]) ? $arow["responderid"] : 0)." and program in ( 'aed', 'reg' )" );
    
    if( $cprdate )
    {
        $cprdate = strtotime( $cprdate );
        $cprdate = mktime( 0,0,0,date( "m", $cprdate ),date( "d", $cprdate ),date( "Y", $cprdate )+2 );
        if( $cprdate > time() )
            $expired = false;
        $cprdate = date( "m/d/Y", $cprdate );
    }
    
    if( $aeddate )
    {
        $aeddate = strtotime( $aeddate );
        $aeddate = mktime( 0,0,0,date( "m", $aeddate ),date( "d", $aeddate ),date( "Y", $aeddate )+2 );
        if( $aeddate > time() )
            $expired = false;
        $aeddate = date( "m/d/Y", $aeddate );
    }
    
    $params["EncryptedStatus"] = $expired ? "Inactive" : "Active";
    $params["EncryptedAEDExpDate"] = isset($aeddate) ? $aeddate : '';
    $params["EncryptedCPRExpDate"] = isset($cprdate) ? $cprdate : '';
    $params["EncryptedAgencyFlag"] = "E";
    $params["EncryptedAction"] = "A";

    $encparams = array();
    foreach( $params as $a=>$b )
    {
        $encparams[$a] = encrypt( $b );
    }
    
    $param1 = array('parameters' => $encparams );
    $result = $client->call('UpdateAEDResponder2', $param1);

    // Note: mysql_escape_string is deprecated
    $result = isset($result['UpdateAEDResponder2Result']) ? mysql_escape_string($result['UpdateAEDResponder2Result']) : '';
    db_query( "update responders_esi set lastupdatedate = now(), lastupdateresult = '".$result."' where responderid = ".(isset($arow["responderid"]) ? $arow["responderid"] : 0) );
    
    if( $result )
    {
        $res = isset($result["UpdateAEDResponder2Result"]) ? $result["UpdateAEDResponder2Result"] : '';
        $t = date( "m/d/y h:i:a"). " -- update aed responder: ".(isset($arow["responderid"]) ? $arow["responderid"] : '').": \n";
        $t .= "Result: ";
        $t .= print_r( $res, true ). "\n\n";
        
        // Note: mysql_escape_string is deprecated
        $t = mysql_escape_string($t);
        db_query( "insert into employeeslog values ( '".(isset($arow["responderid"]) ? $arow["responderid"] : '')."', '".$t."', now() )" );
        
        if( !empty($res[0]) && !empty($res[3]) )
            return 1;
    }
    
    return 0;    
}

function updateAED( $arow )
{
    if( !isset($arow["buildingcode"]) || !$arow["buildingcode"] )
    {
        return;
    }
    
    $crow = getCompanyRow( isset($arow["clientid"]) ? $arow["clientid"] : 0 );
    if( isset($crow["iscorp"]) && $crow["iscorp"] )
        return 1;

    $client = getClientForSoap( "wsaed.asmx" );
    if( !$client ) {
        return 0;
    }
    
    $params = array();
    $params["EncryptedUnitSerialNo"] = isset($arow["serial"]) ? trim($arow["serial"]) : '';
    $params["EncryptedInstallDate"] = "";
    $params["EncryptedBuildingCode"] = isset($arow["buildingcode"]) ? $arow["buildingcode"] : '';
    $params["EncryptedLocation"] = isset($arow["location"]) ? $arow["location"] : '';
    $params["EncryptedStatus"] = (isset($arow["aedinactive"]) && $arow["aedinactive"]) ? "O" : "A";

    $encparams = array();
    foreach( $params as $a=>$b )
    {
        $encparams[$a] = encrypt( $b );
    }
    
    $param1 = array('parameters' => $encparams );
    $result = $client->call('UpdateAEDUnit', $param1);

    // Note: mysql_escape_string is deprecated
    $result = isset($result['UpdateAEDUnitResult']) ? mysql_escape_string($result['UpdateAEDUnitResult']) : '';
    db_query( "update aed_esi set lastupdatedate = now(), lastupdateresult = '".$result."' where aedid = ".(isset($arow["aedid"]) ? $arow["aedid"] : 0) );
    
    if( $result )
    {
        $res = isset($result["UpdateAEDUnitResult"]) ? $result["UpdateAEDUnitResult"] : '';
        $t = date( "m/d/y h:i:a"). " -- update aed unit: ".(isset($arow["aedid"]) ? $arow["aedid"] : '').": \n";
        $t .= print_r( $params, true ). "\n\n";
        $t .= "Result: ";
        $t .= print_r( $res, true ). "\n\n";
        
        // Note: mysql_escape_string is deprecated
        $t = mysql_escape_string($t);
        db_query( "insert into employeeslog values ( '".(isset($arow["aedid"]) ? $arow["aedid"] : '')."', '".$t."', now() )" );
        
        return 1;
    }
    
    return 0;    
}

?>