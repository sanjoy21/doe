<?php
    error_reporting( E_ALL );
    $nologinrequired = 1;

function admiralGatherValues( $classrow, $type )
{
    $ordervalues = array();

// the left is the field name in the XML
    
    $ordervalues["Recipient"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Name"]:$classrow["Delivery Name"];
    $ordervalues["Street2"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Address"]:$classrow["Delivery Address"];
    $ordervalues["City2"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery City"]:$classrow["Delivery City"];
    //    $ordervalues["delive_state"] = $classrow["alt_Delivery Name"]?formatAdmiralState( $classrow["alt_Delivery State"] ):formatAdmiralState( $classrow["Delivery State"] );
    $ordervalues["Zone2"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Zip/Postal Code"]:$classrow["Delivery Zip/Postal Code"];
    //    $ordervalues["deliver_country"] = "US";
    $ordervalues["Phone2"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Phone Number"]:$classrow["Delivery Phone Number"];
    //    $ordervalues["deliver_attention"] = $classrow["alt_Delivery Attention/To See/Contact"]?$classrow["alt_Delivery Attention/To See/Contact"]:$classrow["Delivery Attention/To See/Contact"]; // not sure

    $ordervalues["Comment1"] = $classrow["Pickup Special Instructions"];
    $ordervalues["Comment2"] = $classrow["Delivery Special Instructions"];
    $ordervalues["Pieces"] = $classrow["alt_# of Pieces"]?$classrow["alt_# of Pieces"]:$classrow["# of Pieces"];

     $ordervalues["Sender"] = $classrow["alt_Pickup Attention/To See/Contact"]?$classrow["alt_Pickup Attention/To See/Contact"]:$classrow["Pickup Attention/To See/Contact"];


     $ordervalues["RoundTrip"] = "N";
    $ordervalues["Date1"] = $classrow["Order Date"];
//    $ordervalues["deliver_requested_date"] = $classrow["Order Date"];
    $ordervalues["Time1"] = formatAdmiralTime( $classrow["Pickup Requested Arrival Time"] );
//    $ordervalues["deliver_requested_dep_time"] = formatAdmiralTime( $classrow["Delivery Requested Depart Time"] );

//    $ordervalues["number_of_pieces"] = $classrow["# of Pieces"];
    $ordervalues["Caller"] = $classrow["Requested By"];
    $ordervalues["Sender"] = $classrow["Pickup Name"];
    $ordervalues["Street1"] = $classrow["Pickup Address"];
    $ordervalues["City1"] = $classrow["Pickup City"];
    //    $ordervalues["State1"] = formatAdmiralState( $classrow["Pickup State"] ); // no state? 
    $ordervalues["Zone1"] = $classrow["Pickup Zip"];
    //    $ordervalues["pickup_country"] = "US";
    $ordervalues["Phone1"] = $classrow["Pickup Phone Number"];
    // $lineitems = array();
    // $lineitems[] = array( "item_description"=> "CPR1", "number_of_pieces" => $classrow["alt_# of Pieces"]?$classrow["alt_# of Pieces"]:$classrow["# of Pieces"] );
    
    // $ordervalues["line_items"] = $lineitems;
    $ordervalues["Weight"] = $classrow["alt_Weight"]?$classrow["alt_Weight"]:$classrow["Weight"];
    $ordervalues["Reference"] = $classrow["Customer Reference"];
    //    $ordervalues["bol_number"] = $classrow["BOL Number"]; ????? 
    $ordervalues["Service"] = $classrow["Service Level (Key will be provided)"];
    //    $ordervalues["customer_number"] = $classrow["Customer Number"];
    
    foreach( $ordervalues as $key=>$val )
    {
        if( !strlen( $val ) && !is_array( $val ) )
            unset( $ordervalues[$key] );
    }
    
    return $ordervalues;
}


function bookNewAdmiral( $classrow, $type )
{

    $ordervalues = admiralGatherValues( $classrow, $type );

if( $doingtesting ) // do this if you want to see what would be sent
{
    echo( nl2br(     print_r( $classrow, true ) ) );
    echo( nl2br(     print_r( $ordervalues, true ) ) );
    echo( "<br><br>" );
    return;
    }
    
$values = $ordervalues;

    $val = callAdmiral( "WebTicketSubmit", $values, $classrow[classid], $type );
    // echo( "<br><br> Sent This: <Br>" );
    // echo( json_encode( $values ));
    // echo( "<br><br> Got this response: <Br>" );
    // print_r( $val );
    foreach( $val as $vid=>$valdisp )
    {
        if( $vid == "TXNO" ) return "<font color='red'>" . $valdisp . ":" . $val->error . "</font>";
        return $valdisp;  // not sure here?
    }
    return -1;
}

function formatAdmiralTime( $tm )
{
    if( $tm )
        return date( "H:i", strtotime( "01/31/2014 $tm" ) );
    else
        return "";
}

function formatAdmiralState( $state )
{
    if( trim( strtolower( $state ) ) == "new york" )
        return "NY";
    return $state;
}

function updateAdmiral( $orderid, $classrow = array(), $type )
{
    $ordervalues = admiralGatherValues( $classrow, $type );

//   print_r( $classrow );
//    print_r( $ordervalues );
//    exit;
    $values = array( "order"=>$ordervalues );
//    echo( "<br><br> Sent This: <Br>" );
//    echo( json_encode( $values ));
//    echo( "<br><br> Got this response: <Br>" );
    $val = callAdmiral( "order/$orderid", "PUT", $values, $classrow[classid], $type );
    // foreach( $val as $vid=>$val )
    // {
    //     return $vid; 
    // }
    // return -1;
    // $val = callAdmiral( "order/$orderid", "PUT" );

}

function cancelAdmiral( $admiralorderid, $orderid, $type )
{
    // can't be done
}

function lookupAdmiral( $orderid, $type = "" )
{
    $val = callAdmiral( "order/$orderid", "GET", array(), $orderid, $type );
    return $val;
} 

function getAdmiralFields( $type )
{
if( $type == "incoming" )
{

        // this is going back to ESI
    
	$tmparr = array();
    $tmparr["Return Pick Up Date"] = "Order Date"; // the things on the right are what is on the right of the top function, so you don't need to change them, what is on the left is the saved name in the db
    $tmparr["Requested by"] = "Requested By";
    $tmparr["Customer Reference"] = "Customer Reference";
    $tmparr["BOL Number"] = "BOL Number";
    $tmparr["Delivery Name"] = "Pickup Name";
    $tmparr["Delivery Address"] = "Pickup Address";
    $tmparr["Delivery City"] = "Pickup City";
    $tmparr["Delivery State"] = "Pickup State";
    $tmparr["Delivery Zip"] = "Pickup Zip/Postal Code";
    $tmparr["Delivery Attention"] = "Pickup Attention/To See/Contact";
    $tmparr["Delivery Room"] = "Pickup Room/Floor/Department";
    $tmparr["Delivery Phone"] = "Pickup Phone Number";
    $tmparr["Delivery Extension"] = "Pickup Phone Extension";
    $tmparr["Service Level"] = "Service Level (Key will be provided)";
    $tmparr["Return Service Lev"] = "Return Service level";
    $tmparr["Return Pick up Requested Arrival Time"] = "Pickup Requested Arrival Time";
//    $tmparr["Return Delivery Requested Depart Time"] = "Delivery Requested Depart Time";
    $tmparr["Delivery Special Instructions"] = "Pickup Special Instructions";
    $tmparr["Pick up Name"] = "Delivery Name";
    $tmparr["Pick up Address"] = "Delivery Address";
    $tmparr["Pick up City"] = "Delivery City";
    $tmparr["Pick up State"] = "Delivery State";
    $tmparr["Pick up Zip"] = "Delivery Zip/Postal Code";
    $tmparr["Pick up Contact"] = "Delivery Attention/To See/Contact";
    $tmparr["Pick up room"] = "Delivery Room/Floor/Department";
    $tmparr["Pick up phone number"] = "Delivery Phone Number";
    $tmparr["Pick up extension"] = "Delivery Phone Extension";
    $tmparr["Pick up Special Instructions"] = "Delivery Special Instructions";
    $tmparr["Insurance Amount"] = "Insurance Amount (whole dollars)";
    $tmparr["# of Pieces"] = "# of Pieces";
    $tmparr["Weight"] = "Weight";


    $tmparr["Return Delivery Name"] = "alt_Delivery Name";
    $tmparr["Return Delivery Address"] = "alt_Delivery Address";
    $tmparr["Return Delivery City"] = "alt_Delivery City";
    $tmparr["Return Delivery State"] = "alt_Delivery State";
    $tmparr["Return Delivery Zip"] = "alt_Delivery Zip/Postal Code";
    $tmparr["Return Delivery Attention"] = "alt_Delivery Attention/To See/Contact";
    $tmparr["Return Delivery Room"] = "alt_Delivery Room/Floor/Department";
    $tmparr["Return Delivery Phone"] = "alt_Delivery Phone Number";
    $tmparr["Return Delivery Extension"] = "alt_Delivery Phone Extension";
    $tmparr["Return # of Pieces"] = "alt_# of Pieces";
    $tmparr["Return Weight"] = "alt_Weight";
    
}
else
{

        // this is going out to the client/school

    $tmparr = array();
    $tmparr["Pick Up Date"] = "Order Date"; // the things on the right are what is on the right of the top function
    $tmparr["Requested by"] = "Requested By";
    $tmparr["Customer Reference"] = "Customer Reference";
    $tmparr["BOL Number"] = "BOL Number";
    $tmparr["Pick up Name"] = "Pickup Name";
    $tmparr["Pick up Address"] = "Pickup Address";
    $tmparr["Pick up City"] = "Pickup City";
    $tmparr["Pick up State"] = "Pickup State";
    $tmparr["Pick up Zip"] = "Pickup Zip/Postal Code";
    $tmparr["Pick up Contact"] = "Pickup Attention/To See/Contact";
    $tmparr["Pick up room"] = "Pickup Room/Floor/Department";
    $tmparr["Pick up phone number"] = "Pickup Phone Number";
    $tmparr["Pick up extension"] = "Pickup Phone Extension";
    $tmparr["Service Level"] = "Service Level (Key will be provided)";
    $tmparr["Return Service Lev"] = "Return Service level";
    $tmparr["Pick up Requested Arrival Time"] = "Pickup Requested Arrival Time";
//    $tmparr["Delivery Requested Depart Time"] = "Delivery Requested Depart Time";
    $tmparr["Pick up Special Instructions"] = "Pickup Special Instructions";
    $tmparr["Delivery Name"] = "Delivery Name";
    $tmparr["Delivery Address"] = "Delivery Address";
    $tmparr["Delivery City"] = "Delivery City";
    $tmparr["Delivery State"] = "Delivery State";
    $tmparr["Delivery Zip"] = "Delivery Zip/Postal Code";
    $tmparr["Delivery Attention"] = "Delivery Attention/To See/Contact";
    $tmparr["Delivery Room"] = "Delivery Room/Floor/Department";
    $tmparr["Delivery Phone"] = "Delivery Phone Number";
    $tmparr["Delivery Extension"] = "Delivery Phone Extension";
    $tmparr["Delivery Special Instructions"] = "Delivery Special Instructions";
    $tmparr["Insurance Amount"] = "Insurance Amount (whole dollars)";
    $tmparr["# of Pieces"] = "# of Pieces";
    $tmparr["Return # of Pieces"] = "Return # of Pieces";
    $tmparr["Weight"] = "Weight";
}
return $tmparr;
}

function callAdmiral( $call, $values = array(), $classid = "", $type = "" )
{
    global $i, $type, $session_userid;
    $msvrID = "ADMIRSRV";
    $groupID = "ADMIR2000";
    $user = "EmergencySkills";
    $password = "emer2471";
    $service_url = "http://RushService.RushWeb.com/myservice.asmx/";
    
    $loginmethod = "WebTicketLogin";

    
    if( !$classid ) $classid = $i;
    $logindata = array(
			  "msvrID" => $msvrID,
			  "groupID" => $groupID,
			  "user" => $user,
			  "password" => $password
			  );

    $loginclient = new SoapClient("http://RushService.rushweb.com/MyService.asmx?wsdl" );

    $result = $loginclient->$loginmethod(new SoapParam($logindata, 'Data'));

    // print_r( $logindata );
    // echo "REQUEST:\n" . $loginclient->__getLastRequest() . "\n";
    // echo "RESPONSE:\n" . $loginclient->__getLastResponse() . "\n";
    // print_r( $result );

    $guid = $result->WebTicketLoginResult->guid;
    echo( "guid is: " . $guid );
    //    exit;
    
//    print_r( $values );
//Staging: https://login.beta.datatrac.com
//Production: https://api-ufc.datatrac.com
    // $client = new SoapClient(NULL, array(
    // 					 'location' => $service_url,
    // 					 'uri' => 'http://RushService.rushweb.com/MyService.asmx?wsdl',
    // 					 'trace' => 1,
    // 					 'use' => SOAP_LITERAL)
    // 			     );


    $values["guid"] = $guid;
    
    $data = soapify($values);

    $result = $loginclient->$call(new SoapParam($data, 'Data'));

    echo( "\n\n starting regular \n\n" );
    print_r( $data );
    echo "REQUEST:\n" . $loginclient->__getLastRequest() . "\n";
    echo "RESPONSE:\n" . $loginclient->__getLastResponse() . "\n";
    print_r( $result );

    exit;
    
    
//    echo( $curl_response );
    $typestr = $typ=="incoming"?"return":"";
    db_query( "update class set {$typestr}admiralresponse = '" . mysql_escape_string( $curl_response ) . "' where id = '$classid' " );

    db_query( "insert into admirallog ( classid, method, whensent, who, type, fromdata, retval, admiralcall ) values ( '$classid', '$method', now(), '$session_userid', '$type', '" . mysql_escape_string( print_r( $values, true ) ) . "', '" . mysql_escape_string( $curl_response ) . "', '" . mysql_escape_string( $call ) . "' )" );
    
    file_put_contents( "admiral.log", "response for $i : " . $curl_response . "\n\n", FILE_APPEND );
    
    $xml = json_decode($curl_response);
    return $xml;
    
}

// $id = bookNewAdmiral( array() );
// echo( "the id is: " . $id );

// $find = lookupAdmiral( $id );
// echo( "found it!<br>" );
// print_r( $find );




function soapify(array $data)
{
    return $data;
    foreach ($data as &$value) {
	if (is_array($value)) {
	    $value = soapify($value);
	}
    }

    return new SoapVar($data, SOAP_ENC_OBJECT);
}


$values = array();
$values["date1"] = "02/23/2017";
$values["date2"] = "02/23/2017";
$values["comment1"] = "SYSTEMS TEST. PLEASE DO NOT DISPATCH A COURIER.";
$values["caller"] = "Robert";
$values["snder"] = "Robert";
$values["comment2"] = "Hours: 06:00AM-03:00PM";
$values["recipient"] = "Dummy Dummy";
$values["company2"] = "Dummy";
$values["street2"] = "Dummy DYMMY";
$values["city2"] = "dummy";
$values["zone2"] = "dummy";
$values["pieces"] = "2";
$values["reference"] = "L373509";
$values["weight"] = "12";
$values["service"] = "RESIZN";
$values["company1"] = "ImKlein";
$values["street1"] = "BACK DOOR, 1875 NORTHSIDE";
$values["city1"] = "CHATTAHOOCHEE";
$values["zone1"] = "30318";
$values["time1"] = "14:00";
$values["time2"] = "15:00";
$values["phone1"] = "404-351-4444";
$values["phone2"] = "Dummy";
$values["email"] = "";
$values["roundtrip"] = "";

echo( "before" );
callAdmiral( "WebTicketSubmit", $values );
echo( "after" );

?>
