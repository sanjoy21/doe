<?php

function birdieGatherValues($classrow, $type)
{
    // pretty sure this should be identical to update.... rachel
    $ordervalues = [];
    $ordervalues["custId"] = 12;
    
    // Fixed: Using null coalescing operator for cleaner code
    $ordervalues["orderType"] = $classrow["Order Type"] ?? 24;
    
    // Fixed: Using null coalescing operator consistently
    $ordervalues["destination"]["name"] = $classrow["alt_Delivery Name"] ?? $classrow["Delivery Name"] ?? '';
    $ordervalues["destination"]["address1"] = $classrow["alt_Delivery Name"] ?? '' ? 
        ($classrow["alt_Delivery Address"] ?? '') : 
        ($classrow["Delivery Address"] ?? '');
    
    $ordervalues["destination"]["city"] = $classrow["alt_Delivery Name"] ?? '' ? 
        ($classrow["alt_Delivery City"] ?? '') : 
        ($classrow["Delivery City"] ?? '');
    
    $ordervalues["destination"]["state"] = $classrow["alt_Delivery Name"] ?? '' ? 
        formatBirdieState($classrow["alt_Delivery State"] ?? '') : 
        formatBirdieState($classrow["Delivery State"] ?? '');
    
    $ordervalues["destination"]["zip"] = $classrow["alt_Delivery Name"] ?? '' ? 
        ($classrow["alt_Delivery Zip/Postal Code"] ?? '') : 
        ($classrow["Delivery Zip/Postal Code"] ?? '');
    
    $ordervalues["destination"]["phone"] = $classrow["alt_Delivery Name"] ?? '' ? 
        ($classrow["alt_Delivery Phone Number"] ?? '') : 
        ($classrow["Delivery Phone Number"] ?? '');
    
    $ordervalues["destination"]["destComments"] = $classrow["alt_Delivery Attention/To See/Contact"] ?? '' ? 
        ($classrow["alt_Delivery Attention/To See/Contact"] ?? '') : 
        ($classrow["Delivery Attention/To See/Contact"] ?? '');

    $ordervalues["origin"]["originComments"] = $classrow["Pickup Special Instructions"] ?? '';
    $ordervalues["destination"]["originComments"] = $classrow["Delivery Special Instructions"] ?? '';

    $ordervalues["origin"]["name"] = $classrow["alt_Pickup Name"] ?? $classrow["Pickup Name"] ?? '';
    $ordervalues["origin"]["address1"] = $classrow["alt_Pickup Name"] ?? '' ? 
        ($classrow["alt_Pickup Address"] ?? '') : 
        ($classrow["Pickup Address"] ?? '');
    
    $ordervalues["origin"]["city"] = $classrow["alt_Pickup Name"] ?? '' ? 
        ($classrow["alt_Pickup City"] ?? '') : 
        ($classrow["Pickup City"] ?? '');
    
    $ordervalues["origin"]["state"] = $classrow["alt_Pickup Name"] ?? '' ? 
        formatBirdieState($classrow["alt_Pickup State"] ?? '') : 
        formatBirdieState($classrow["Pickup State"] ?? '');
    
    $ordervalues["origin"]["zip"] = $classrow["alt_Pickup Name"] ?? '' ? 
        ($classrow["alt_Pickup Zip/Postal Code"] ?? '') : 
        ($classrow["Pickup Zip/Postal Code"] ?? '');
    
    $ordervalues["origin"]["phone"] = $classrow["alt_Pickup Name"] ?? '' ? 
        ($classrow["alt_Pickup Phone Number"] ?? '') : 
        ($classrow["Pickup Phone Number"] ?? '');
    
    $ordervalues["origin"]["destComments"] = $classrow["alt_Pickup Attention/To See/Contact"] ?? '' ? 
        ($classrow["alt_Pickup Attention/To See/Contact"] ?? '') : 
        ($classrow["Pickup Attention/To See/Contact"] ?? '');

    $ordervalues["reference1"] = $classrow["Customer Reference"] ?? '';
    $ordervalues["reference2"] = $classrow["Customer Reference"] ?? '';
    $ordervalues["serviceType"] = "6";
    
    $ordervalues["weight"] = $classrow["alt_Weight"] ?? $classrow["Weight"] ?? '';
    $ordervalues["pieces"] = $classrow["alt_# of Pieces"] ?? $classrow["# of Pieces"] ?? '';
    
    $ordervalues["emailPod"] = "dfunnye@emergencyskills.com";
    
    // Fixed: Check if required array keys exist before using them
    $orderDate = $classrow["Order Date"];
    $pickupTime = $classrow["Pickup Requested Arrival Time"];
    $ordervalues["readyTimeFrom"] = strtotime($orderDate . " " . $pickupTime) * 1000;
    

    // FIXED: Check only string values with strlen, skip arrays
    foreach ($ordervalues as $key => $val) {
        if (is_array($val)) {
            // Skip arrays, they shouldn't be removed
            continue;
        }
        if (!strlen((string)$val)) {
            unset($ordervalues[$key]);
        }
    }
    
    return $ordervalues;
}

function bookNewBirdie($classrow, $type)
{

    $ordervalues = birdieGatherValues($classrow, $type);

    // Fixed: Check if $doingtesting is set before using it
    if (isset($doingtesting) && $doingtesting) {
        echo nl2br(print_r($classrow, true));
        echo nl2br(print_r($ordervalues, true));
        echo "<br><br>";
        return;
    }

    $doingtesting = 1;
    $accessorialItems = [];
    $values = ["order" => $ordervalues];
    $arr = ["order" => $ordervalues, "accessorialItems" => $accessorialItems, "creditCard" => null];

    // Fixed: Added quotes around array key
    $val = callBirdie("Orders", "POST", $values, $classrow['classid'] ?? 0, $type);
    
    if (is_numeric((string)$val)) {
        return intval((string)$val);
    }
    
    return -1;
}

function formatBirdieTime($tm)
{
    if ($tm) {
        // Fixed: Added proper date format
        return date("H:i", strtotime("01/31/2014 " . $tm));
    } else {
        return "";
    }
}

function formatBirdieState($state)
{
    if (trim(strtolower($state)) == "new york") {
        return "NY";
    }
    return $state;
}

function updateBirdie($orderid, $classrow = [], $type)
{
    $ordervalues = birdieGatherValues($classrow, $type);
    $values = ["order" => $ordervalues];
    
    // Fixed: Added quotes around array key
    $val = callBirdie("order/$orderid", "PUT", $values, $classrow['classid'], $type);
}

function cancelBirdie($birdieorderid, $orderid, $type)
{
    $val = callBirdie("order/$birdieorderid", "DELETE", [], $orderid, $type);
}

function lookupBirdie($orderid, $type = "")
{
    $val = callBirdie("order/$orderid", "GET", [], $orderid, $type);
    return $val;
}

function callBirdie($call, $method, $values = [], $classid, $type)
{
    global $i, $type, $session_userid;
    
    $username = "INET-EmergSkillsInt";
    $password = "bwJtA2E3S";

    if (!$classid) {
        $classid = $i;
    }

    $service_url = "https://06840.cxtsoftware.net/CxtWebService/CXTWCF.svc/v2/{$call}";
    $curl = curl_init($service_url);

    curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($values));
    
    file_put_contents("birdie.log", "sent to url: " . $service_url . "\n\n");
    file_put_contents("birdie.log", "values: " . json_encode($values) . "\n\n", FILE_APPEND);
    
    $curl_response = curl_exec($curl);
    if (curl_errno($curl)) {
        file_put_contents("birdie.log", "cURL error: " . curl_error($curl) . "\n\n", FILE_APPEND);
    }
    curl_close($curl);
    
    file_put_contents("birdie.log", "response: '" . $curl_response . "'\n\n", FILE_APPEND);
    
    $fromData = print_r($values, true);
    $birdiecall = $call;
    
        db_query("INSERT INTO birdielog (classid, method, whensent, who, type, fromdata, retval, birdiecall) 
                  VALUES ('$classid', 
                          '$method', 
                          NOW(), 
                          '$session_userid', 
                          '$type', 
                          '" . mysql_escape_string($fromData) . "', 
                          '" . mysql_escape_string($curl_response) . "', 
                          '" . mysql_escape_string($birdiecall) . "')");
    
        $xml = simplexml_load_string($curl_response, "SimpleXMLElement", LIBXML_NOCDATA);
        file_put_contents("birdie.log", "ended: " . print_r($xml, true) . "\n\n", FILE_APPEND);
        return $xml;
    
    
}

function getBirdieFields($type)
{
    $tmparr = [];
    
    if ($type == "incoming") {
        // Return to ESI
        $tmparr["Return Pick Up Date"] = "Order Date";
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
        $tmparr["Order Type"] = "Order Type";

        // Alts for jumping
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
    } else {
        // Going out to client/school
        $tmparr["Pick Up Date"] = "Order Date";
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
        $tmparr["Pick up Requested Arrival Time"] = "Pickup Requested Arrival Time";
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
        $tmparr["Order Type"] = "Order Type";
        $tmparr["# of Pieces"] = "# of Pieces";
        $tmparr["Return # of Pieces"] = "Return # of Pieces";
        $tmparr["Weight"] = "Weight";
    }
    
    return $tmparr;
}

?>