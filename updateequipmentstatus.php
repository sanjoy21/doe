<?php 
include "mysql.php"; 

// --- Security Helper Functions ---
// IMPORTANT: In a real-world application, this must use mysqli_real_escape_string or prepared statements.
if (!function_exists('db_escape_or_placeholder')) {
    function db_escape_or_placeholder($str) {
        return addslashes((string)($str ?? '')); 
    }
}
// Helper for HTML escaping (XSS mitigation)
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
// --- End Helper Functions ---

if( isset($_POST['update']) ) // Use isset and $_POST for clarity
{
    // Sanitize user inputs
    $status = db_escape_or_placeholder($_POST['status'] ?? '');
    $newbag = db_escape_or_placeholder($_POST['newbag'] ?? '');
    // Ensure class ID is an integer
    $newclassid = (int)($_POST['newclassid'] ?? 0);
    $eids = $_POST['eids'] ?? [];

    foreach( $eids as $barcode_raw )
    {
        if( !trim( $barcode_raw ) ) continue;
        
        // SQLi Mitigation: Escape the barcode input before using it in the query
        $barcode = db_escape_or_placeholder(trim($barcode_raw));

        // Use the escaped barcode in the query
        $equipmentid = db_query_first_cell( "select id from equipment where barcode = '$barcode' and retired = 0" );

        // We pass the raw (unescaped) variables to addEquipmentStatus 
        // as it should handle escaping/sanitization for its own query internally.
        // If addEquipmentStatus does NOT sanitize its inputs, it would also be vulnerable.
        addEquipmentStatus( $equipmentid, $status, $newbag, $newclassid );
    }

$redirect = "equipment.php";
    Header( "location: $redirect " );
    exit;
}

include "ssi/top.php";
?>
<form method="post" onSubmit='return checkOK( this )' >
<input type="hidden" name ="update" value="true">

</head>

<?php
if( !$redirect )
$redirect="equipment.php";
// Assume $equipmentstatuses is defined and safe to use
unset( $equipmentstatuses["Created"] );

// Assume $barcodes is defined from previous session/request or is an empty array
$barcodes = $barcodes ?? [];
?>

<?php if( $specialadmin ) { ?>
<table class="table3" cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="equipment.php">&laquo; Back to <?=h(getSchoolStr( "All Equipment" ))?></a></strong></span></td>
</tr>
</table>
<?php } ?>
<table class="table3" cellpadding="5" cellspacing="1" border="0" width="100%">

<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Status *:</strong><br><input id="status" type="text" size="15" id="status" name="status" onChange="if( checkStatus( this.value ) ) { addNewBarcode() } " onkeypress="this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" style="font-size: 10px; font-family: verdana;"><Br>
Valid Statuses: <?=h(implode( ", ", $equipmentstatuses ))?>
</span></td>
</tr>
<tr><td id="extrainfo">
</td></tr>
<tr>
<td valign="top" bgcolor="#E2DFDF">
<span class="copy"><strong>Barcodes*:</strong><br>

<div id="barcodesdiv">
<?php
foreach( $barcodes as $b )
{
// XSS Mitigation: Escaping barcode value
echo ("\n<br>Barcode: <input type='text' class='eids' name='eids[]' value=\"" . h($b) . "\" onChange='validateCode( this )'>" );
}
?>
</div>
</span></td>
</tr>
<input type='hidden' name='oktosubmit' id='oktosubmit' value=''>
<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
<br>
<div align="center">
<input type="submit" onMouseDown='document.getElementById( "oktosubmit" ).value = 1; return true' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" style="display:none" value="do nothing">
</div>
</td>
</tr>
</table>
<br><br>
<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>

<script language='javascript'>
function checkStatus( status )
{
$("#extrainfo").html("" );
<?php
foreach( $equipmentstatuses as $ebarcode=>$eval )
{
if( $eval == "Created" ) continue;
?>
if( status == "<?=$ebarcode?>" )
{
<?php if( $eval == "Packed" )
{
?>
$("#extrainfo").html( "Bag: <input type='text' name='newbag'>" );
<?php
}
?>
<?php if( $eval == "At A Class" )
{
?>
$("#extrainfo").html( "Class ID: <input type='text' name='newclassid'>" );
<?php
}
?>
return true; 
}
<?php
}
?>

// alert( status + " is not a valid status" );
// $("input[name=status]").val("");
setTimeout(function() { document.getElementById("status").focus(); }, 10);
return false;
}

function addNewBarcode()
{
var tmpval = $(".eids").last().val();
if( tmpval > "" || $(".eids").length < 1 )
{
// XSS mitigation for JavaScript output: No dynamic input here, but careful if $barcodes were dynamically generated
var val = "\n<br>Barcode: <input type='text' class='eids' name='eids[]' onpaste='validateCode( this, false );' oninput='validateCode( this, false );' onChange='validateCode( this, true )'>";
$("#barcodesdiv").append( val );
setTimeout(function() { $(".eids").last().focus(); }, 100);
}

}

function validateCode( ele, doalert )
{
val = ele.value;
if( val.trim() == "" ) return;

if( doalert || val.length > 5 )
{
        // CSRF/Security Note: checkbarcode.php must also validate and escape $_POST['code']
$.ajax( { type: "POST", url: "checkbarcode.php", dataType:"json", data: { code: val } } ).success( function( data ) {
if( data.status != "ok" )
{
if( doalert )
{
// XSS Mitigation: Use ele.value (raw text) in alert, but ensure data.status is safe on the server side
alert( ele.value + " is not a valid barcode" );
ele.value = "";
setTimeout(function() { $(".eids").last().focus(); }, 10);
}
}
else
{
addNewBarcode();
}

} );;
}
}
function checkOK( frm )
{
if( $("input[name=status]").val() == "" )
{
alert( "Status is required." );
return false;

}
if( $("input[name=oktosubmit]").val() == "" )
{
return false;
}

return true;
}
$(document).ready(function() {
$(window).keydown(function(event){
if(event.keyCode == 13) {
event.preventDefault();
return false;
}
});
});
</script>
</body>
</html>