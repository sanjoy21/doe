<?php 
// $nologinrequired = true;
// require "mysql.php";
// require_once "xpo/api.php";

// $classid_safe = $classid ?? null;
// $type_safe = $type ?? null;

// $classrow = getClassRow( $classid_safe );

// if( $type_safe == "incoming" )
// {
//     $theid = $classrow["returnxpoid"] ?? '-1'; 

//     if( $theid <> "-1" && $theid <> "0" && $theid <> null ) 
//     {
//         cancelXPO( $theid, $classid_safe );

//         db_query( "update class set returnxpoid = 0, returnxpodatesent = now() where id = " . (int)$classid_safe );

//         db_query( "update class set xpoid = 0, xpodatesent = now() where xpoid = '" . $theid . "'" );
//     }

// }
// else if( $type_safe == "outgoing" )
// {

//     $theid = $classrow["xpoid"] ?? '-1';
    

//     if( $theid <> "-1" && $theid <> "0" && $theid <> null ) // Added check for "0" and null for robustness
//     {
        
//         cancelXPO( $theid, $classid_safe );
//         db_query( "update class set xpoid = 0, xpodatesent = now() where id = " . (int)$classid_safe );
//         db_query( "update class set returnxpoid = 0, returnxpodatesent = now() where returnxpoid = '" . $theid . "'" );
//         db_query( "delete from class_info where name = 'jumpingfrom' and value = '" . $theid . "'" );
//     }
// }

?>