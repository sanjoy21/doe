<?php 
$nologinrequired = true;

require_once "mysql.php";
include "birdie/api.php";

$accessorialItems = array();

$classrow = getClassRow( 37148 );

// The entire block for creating a test order is wrapped in an 'if (1 == 0)' which prevents execution.
if( 1 == 0 )
{
    // PHP 8.2 Fix: Use standard array syntax and ensure consistent quoting (though not strictly necessary here, it's safer)
    $order = array();
    $order["custId"]= "1014";
    // Using $order['readyTimeFrom'] is safer than $order["readyTimeFrom"] in some contexts, but both work with string keys.
    $order["readyTimeFrom"]= strtotime( "+ 2 days" ) * 1000 ;
    $order["orderType"]= 1014;
    $order["origin"]= array(
                "id"=> null,
                "name"=> "Testing 1",
                "address1"=> "412 Española Way",
                "address2"=> null,
                "city"=> "Miami Beach",
                "state"=> "FL",
                "zip"=> "37129",
                "plus4"=> null,
                "validated"=> 0,
                "originComments"=> null,
                "destComments"=> null,
                "lat"=> null,
                "lon"=> null,
                "phone"=> null,
                "country"=> 0
    );
    $order["destination"]= array(
                "id"=> null,
                "name"=> "Testing 1",
                "address1"=> "750 Bass Pro Dr NE",
                "address2"=> null,
                "city"=> "PAlm Bay",
                "state"=> "FL",
                "zip"=> "32905",
                "plus4"=> null,
                "validated"=> 0,
                "originComments"=> null,
                "destComments"=> null,
                "lat"=> null,
                "lon"=> null,
                "phone"=> null,
                "country"=> 0
            );

    $arr = array( "order"=> $order, "accessorialItems"=> $accessorialItems, "creditCard"=>null);
    $val = callBirdie( "Orders", "POST", $arr, 0, "call" );
    echo( htmlspecialchars($val) );
}
else
{
    // bookNewBirdie( $classrow, "outgoing" );
    
    sendClassesToBirdie( array( 37185 ), "outgoing" );
}
?>