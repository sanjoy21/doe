<?php


function xpoGatherValues( $classrow, $type )
{
        // pretty sure this should be identical to update.... rachel
    $ordervalues = array();

    $ordervalues["deliver_name"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Name"]:$classrow["Delivery Name"];
    $ordervalues["deliver_address"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Address"]:$classrow["Delivery Address"];
    $ordervalues["deliver_city"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery City"]:$classrow["Delivery City"];
    $ordervalues["delive_state"] = $classrow["alt_Delivery Name"]?formatXPOState( $classrow["alt_Delivery State"] ):formatXPOState( $classrow["Delivery State"] );
    $ordervalues["deliver_zip"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Zip/Postal Code"]:$classrow["Delivery Zip/Postal Code"];
    $ordervalues["deliver_country"] = "US";
    $ordervalues["deliver_phone"] = $classrow["alt_Delivery Name"]?$classrow["alt_Delivery Phone Number"]:$classrow["Delivery Phone Number"];
    $ordervalues["deliver_attention"] = $classrow["alt_Delivery Attention/To See/Contact"]?$classrow["alt_Delivery Attention/To See/Contact"]:$classrow["Delivery Attention/To See/Contact"];

    $ordervalues["pickup_special_instr_long"] = $classrow["Pickup Special Instructions"];
    $ordervalues["deliver_special_instr_long"] = $classrow["Delivery Special Instructions"];
    $ordervalues["number_of_pieces"] = $classrow["alt_# of Pieces"]?$classrow["alt_# of Pieces"]:$classrow["# of Pieces"];
    $ordervalues["ordered_by"] = $classrow["Requested By"];

// adding so if we want to deliver somewhere else 
    
    $ordervalues["pickup_name"] = $classrow["alt_Pickup Name"]?$classrow["alt_Pickup Name"]:$classrow["Pickup Name"];
    $ordervalues["pickup_address"] = $classrow["alt_Pickup Name"]?$classrow["alt_Pickup Address"]:$classrow["Pickup Address"];
    $ordervalues["pickup_city"] = $classrow["alt_Pickup Name"]?$classrow["alt_Pickup City"]:$classrow["Pickup City"];
    $ordervalues["pickup_state"] = $classrow["alt_Pickup Name"]?formatXPOState( $classrow["alt_Pickup State"] ):formatXPOState( $classrow["Pickup State"] );
    $ordervalues["pickup_zip"] = $classrow["alt_Pickup Name"]?$classrow["alt_Pickup Zip/Postal Code"]:$classrow["Pickup Zip/Postal Code"];
    $ordervalues["pickup_country"] = "US";
    $ordervalues["pickup_phone"] = $classrow["alt_Pickup Name"]?$classrow["alt_Pickup Phone Number"]:$classrow["Pickup Phone Number"];
    $ordervalues["pickup_attention"] = $classrow["alt_Pickup Attention/To See/Contact"]?$classrow["alt_Pickup Attention/To See/Contact"]:$classrow["Pickup Attention/To See/Contact"];


    $ordervalues["pickup_requested_date"] = $classrow["Order Date"];
//    $ordervalues["deliver_requested_date"] = $classrow["Order Date"];
    $ordervalues["pickup_requested_arr_time"] = formatXPOTime( $classrow["Pickup Requested Arrival Time"] );
//    $ordervalues["deliver_requested_dep_time"] = formatXPOTime( $classrow["Delivery Requested Depart Time"] );

//    $ordervalues["number_of_pieces"] = $classrow["# of Pieces"];
    $ordervalues["ordered_by"] = $classrow["Requested By"];
    $ordervalues["pickup_name"] = $classrow["Pickup Name"];
    $ordervalues["pickup_address"] = $classrow["Pickup Address"];
    $ordervalues["pickup_city"] = $classrow["Pickup City"];
    $ordervalues["pickup_state"] = formatXPOState( $classrow["Pickup State"] );
    $ordervalues["pickup_zip"] = $classrow["Pickup Zip"];
    $ordervalues["pickup_country"] = "US";
    $ordervalues["pickup_phone"] = $classrow["Pickup Phone Number"];
    $lineitems = array();
    $lineitems[] = array( "item_description"=> "CPR1", "number_of_pieces" => $classrow["alt_# of Pieces"]?$classrow["alt_# of Pieces"]:$classrow["# of Pieces"] );
    
    $ordervalues["line_items"] = $lineitems;
    $ordervalues["weight"] = $classrow["alt_Weight"]?$classrow["alt_Weight"]:$classrow["Weight"];
    $ordervalues["reference"] = $classrow["Customer Reference"];
    $ordervalues["bol_number"] = $classrow["BOL Number"];
    $ordervalues["service_level"] = $classrow["Service Level (Key will be provided)"];
    $ordervalues["customer_number"] = $classrow["Customer Number"];
    
    foreach( $ordervalues as $key=>$val )
    {
        if( !strlen( $val ) && !is_array( $val ) )
            unset( $ordervalues[$key] );
    }
    
    return $ordervalues;
}


function bookNewXPO( $classrow, $type )
{

    $ordervalues = xpoGatherValues( $classrow, $type );

if( $doingtesting ) // do this if you want to see what would be sent
{
    echo( nl2br(     print_r( $classrow, true ) ) );
    echo( nl2br(     print_r( $ordervalues, true ) ) );
    echo( "<br><br>" );
    return;
    }
    
    $values = array( "order"=>$ordervalues );

    $val = callXPO( "order", "POST", $values, $classrow[classid], $type );
    // echo( "<br><br> Sent This: <Br>" );
    // echo( json_encode( $values ));
    // echo( "<br><br> Got this response: <Br>" );
    // print_r( $val );
    foreach( $val as $vid=>$valdisp )
    {
        if( $vid == "status" ) return "<font color='red'>" . $valdisp . ":" . $val->error . "</font>";
        return $vid; 
    }
    return -1;
}

function formatXPOTime( $tm )
{
    if( $tm )
        return date( "H:i", strtotime( "01/31/2014 $tm" ) );
    else
        return "";
}

function formatXPOState( $state )
{
    if( trim( strtolower( $state ) ) == "new york" )
        return "NY";
    return $state;
}

function updateXPO( $orderid, $classrow = array(), $type )
{
    $ordervalues = xpoGatherValues( $classrow, $type );

//   print_r( $classrow );
//    print_r( $ordervalues );
//    exit;
    $values = array( "order"=>$ordervalues );
//    echo( "<br><br> Sent This: <Br>" );
//    echo( json_encode( $values ));
//    echo( "<br><br> Got this response: <Br>" );
    $val = callXPO( "order/$orderid", "PUT", $values, $classrow[classid], $type );
    // foreach( $val as $vid=>$val )
    // {
    //     return $vid; 
    // }
    // return -1;
    // $val = callXPO( "order/$orderid", "PUT" );

}

function cancelXPO( $xpoorderid, $orderid, $type )
{
    $val = callXPO( "order/$xpoorderid", "DELETE", array(), $orderid, $type );

}

function lookupXPO( $orderid, $type = "" )
{
    $val = callXPO( "order/$orderid", "GET", array(), $orderid, $type );
    return $val;
} 


function callXPO( $call, $method, $values = array(), $classid, $type )
{
    global $i, $type, $session_userid;
    $username = "emskills@xpo.com";
    $password = "a8D6pnSr";

    if( !$classid ) $classid = $i;
    
//    print_r( $values );
//Staging: https://login.beta.datatrac.com
//Production: https://api-ufc.datatrac.com

//    $service_url = "https://login.beta.datatrac.com/rest/{$call}";
    $service_url = "https://api-ufc.datatrac.com/rest/{$call}";
    $curl = curl_init($service_url);

    curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode( $values ));
    file_put_contents( "xpo.log", $i . ": " . json_encode( $values ) . "\n\n", FILE_APPEND );
    $curl_response = curl_exec($curl);
    curl_close($curl);
//    echo( $curl_response );
    $typestr = $typ=="incoming"?"return":"";
    db_query( "update class set {$typestr}xporesponse = '" . mysql_escape_string( $curl_response ) . "' where id = '$classid' " );

    db_query( "insert into xpolog ( classid, method, whensent, who, type, fromdata, retval, xpocall ) values ( '$classid', '$method', now(), '$session_userid', '$type', '" . mysql_escape_string( print_r( $values, true ) ) . "', '" . mysql_escape_string( $curl_response ) . "', '" . mysql_escape_string( $call ) . "' )" );
    
    file_put_contents( "xpo.log", "response for $i : " . $curl_response . "\n\n", FILE_APPEND );
    
    $xml = json_decode($curl_response);
    return $xml;
    
}

// $id = bookNewXPO( array() );
// echo( "the id is: " . $id );

// $find = lookupXPO( $id );
// echo( "found it!<br>" );
// print_r( $find );


function getXPOFields( $type )
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

?>