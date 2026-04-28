<?php 
$nologinrequired = true;
require "mysql.php";
require_once "birdie/api.php";

$classid_safe = $classid ?? null;
$type_safe = $type ?? null;

$classrow = getClassRow( $classid_safe );

if( $type_safe == "incoming" )
{
    $theid = $classrow["returnbirdieid"] ?? '-1'; 
    
    if( $theid <> "-1" && $theid <> "0" && $theid <> null )
    {
        cancelBirdie( $theid, $classid_safe ); 
        db_query( "update class set returnbirdieid = 0, returnbirdiedatesent = now() where id = " . (int)$classid_safe );
        db_query( "update class set birdieid = 0, birdiedatesent = now() where birdieid = '" . $theid . "'" );
    }

}
else if( $type_safe == "outgoing" )
{
    $theid = $classrow["birdieid"] ?? '-1';
    
    if( $theid <> "-1" && $theid <> "0" && $theid <> null )
    {
        cancelBirdie( $theid, $classid_safe );
        db_query( "update class set birdieid = 0, birdiedatesent = now() where id = " . (int)$classid_safe );
        db_query( "update class set returnbirdieid = 0, returnbirdiedatesent = now() where returnbirdieid = '" . $theid . "'" );
        db_query( "delete from class_info where name = 'jumpingfrom' and value = '" . $theid . "'" );
    }
}

?>