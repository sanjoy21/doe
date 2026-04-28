<?php 
$nologinrequired = true;
include "mysql.php";

// Safely retrieve the external variable 'val' (the code)
$val = $_REQUEST['val'] ?? ($val ?? null); 
$val = trim( $val );

// Get the database connection link for escaping strings
$db_link = $GLOBALS['link'] ?? $link; 

// Function to safely escape string for JavaScript output
function js_escape($str) {
    // Escape backslashes, quotes, and newlines for use inside JS strings
    return str_replace(["\\", "'", "\n", "\r"], ["\\\\", "\\'", "\\n", ""], $str ?? '');
}

if( $val )
{
    // Safety: Escape the input value for SQL query
    $safe_val = mysqli_real_escape_string($db_link, $val);
    
    // --- 1. Check if code exists in registration table (tsi_registrants) ---
    $r = db_query_first( "SELECT * FROM tsi_registrants WHERE acceptcode = '{$safe_val}'" );
    
    // --- 2. Check if code is already used in responders table (responders_esi) ---
    $r2 = db_query_first( "SELECT * FROM responders_esi WHERE couponcode = '{$safe_val}'" );
    
    // Get values, safely escaped for JS output
    $r_acceptcode = $r['acceptcode'] ?? null;
    $r2_responderid = $r2['responderid'] ?? null;
    
    if( $r_acceptcode && !$r2_responderid ) 
    {
        // --- Code is Valid and Unused: Pre-fill Form and Show Elements ---
        
        // Retrieve and escape all relevant data for insertion into JS
        $js_firstname = js_escape($r['firstname'] ?? '');
        $js_lastname = js_escape($r['lastname'] ?? '');
        $js_filenumber = js_escape($r['filenumber'] ?? '');
        $js_club = js_escape($r['club'] ?? '');
        $js_gm = js_escape($r['gm'] ?? '');
        $js_phone = js_escape($r['phone'] ?? '');
        $js_email = js_escape($r['email'] ?? '');
        
        // Output JavaScript commands
        echo( "\$('input[name=\"firstname\"]').val( '{$js_firstname}' );\n" );
        echo( "\$('input[name=\"lastname\"]').val( '{$js_lastname}' );\n" );
        echo( "\$('input[name=\"employeeid\"]').val( '{$js_filenumber}' );\n" );
        echo( "\$('input[id=\"tmpschoolname\"]').val( '{$js_club}' );\n" );
        echo( "\$('input[name=\"managername\"]').val( '{$js_gm}' );\n" );
        echo( "\$('input[name=\"phone\"]').val( '{$js_phone}' );\n" );
        echo( "\$('input[name=\"email\"]').val( '{$js_email}' );\n" );
        
        echo( "\$('#tsiform').css( \"display\", \"block\" );\n" );
        echo( "\$('#registerimg').css( \"display\", \"block\" );\n" );
        echo( "updateCompanies(); \n" );
        echo( "$(\"#ccerr\").html( \"\" );\n" );
    }
    else if( !$r_acceptcode )
    {
        // --- Code Not Found ---
        echo( "$(\"#ccerr\").html( \"<font color='red'> Coupon code not found.<br></font>\" );\n" );
    }
    else if( $r2_responderid )
    {
        // --- Code Already Used ---
        echo( "$(\"#ccerr\").html( \"<font color='red'> Coupon code already used.<br></font>\" );\n" );
    }
}
?>