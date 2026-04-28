<?php 
$nologinrequired = true;
include "mysql.php";

// Safely retrieve external variables
$val = $_REQUEST['val'] ?? ($val ?? null); 
$classid = $_REQUEST['classid'] ?? ($classid ?? null); 

// Function to safely escape string for JavaScript output
function js_escape($str) {
    // Escape backslashes, quotes, and newlines for use inside JS strings
    return str_replace(["\\", "'", "\n", "\r"], ["\\\\", "\\'", "\\n", ""], $str ?? '');
}

// --- 1. Get Class and Company Data ---
// Assuming getClassRow() and getCompanyName() handle database calls securely and $val is the class ID.
$r = getClassRow( $val ); 
$companyname = getCompanyName( $r['companyid'] ?? null );

// --- 2. Prepare Data for JavaScript Output ---
$js_companyname = js_escape($companyname);
$js_attention = js_escape(($r['firstname'] ?? '') . ' ' . ($r['lastname'] ?? ''));
$js_location = js_escape($r['training_location'] ?? '');
$js_city = js_escape($r['training_city'] ?? '');
$js_state = js_escape($r['training_state'] ?? '');
$js_zip = js_escape($r['training_zip'] ?? '');
$js_phone = js_escape($r['phone'] ?? '');
$js_phone_ext = js_escape($r['phone_ext'] ?? '');
$js_classid = js_escape($classid);

// --- 3. Output JavaScript Commands to Pre-fill Form Fields ---
// Note: Field IDs are constructed using the class ID variable.

echo( "\$('input[id=\"{$js_classid}_Return Delivery Name\"]').val( '{$js_companyname}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Attention\"]').val( '{$js_attention}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Address\"]').val( '{$js_location}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery City\"]').val( '{$js_city}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery State\"]').val( '{$js_state}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Zip\"]').val( '{$js_zip}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Room\"]').val( 'Main Office' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Phone\"]').val( '{$js_phone}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return # of Pieces\"]').val( '5' );\n" );
echo( "\$('input[id=\"{$js_classid}_Customer Reference\"]').val( '5 Bags CPR TRNG EQUIP' );\n" );
echo( "\$('input[id=\"{$js_classid}_# of Pieces\"]').val( '5' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Phone Ext\"]').val( '{$js_phone_ext}' );\n" );
echo( "\$('input[id=\"{$js_classid}_Return Delivery Special Instructions\"]').val( '5 BAGS CPR EQUIP' );\n" );
echo( "\$('input[id=\"{$js_classid}_Delivery Special Instructions\"]').val( '5 BAGS CPR EQUIP' );\n" );
echo( "\$('input[id=\"{$js_classid}_Pick up Special Instructions\"]').val( '5 BAGS CPR EQUIP' );\n" );
echo( "\$('input[id=\"{$js_classid}_Weight\"]').val( '80' );\n" );

?>