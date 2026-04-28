<?php
require_once('mysql.php');

$doctypes = array();
$doctypes['HR'] = "Human Resources";
$doctypes['II'] = "Instructor Information";
$doctypes['GI'] = "General Information";
$doctypes['CS'] = "Classroom Specifics";

if( isset($addnew) && $addnew )
{
    if( isset($_FILES["newfile"]) && isset($_FILES["newfile"]["name"]) && isset($_FILES["newfile"]["tmp_name"]) )
    {
        $path = $_FILES["newfile"]["name"];
        $path_safe = basename($path); // Sanitize filename
        move_uploaded_file( $_FILES["newfile"]["tmp_name"], "uploadedpdfs/$path_safe" );
        $displayname_safe = isset($displayname) ? $displayname : '';
        $newtype_safe = isset($newtype) ? $newtype : '';
        db_query( "insert into esidocuments ( path, displayname, type) values( '$path_safe', '$displayname_safe', '$newtype_safe'  )" );
    }
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from esidocuments where id = '". $del_safe ."' " );
}

$notavail = db_query_rows( "select * from esidocuments order by type, displayname, orderby" );

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">UPLOADED FILES</span></strong>

<p>

<span class="copy">

<table class="table3" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top">
<form method='post' enctype='multipart/form-data' >
<!--start center content-->
<table  class="table3">
<tr><th class='copy'>Name</th><th class='copy'>File</th><th class='copy'>Type</th><th class='copy'>Action</th></tr>
<?php 
if( isset($notavail) && is_array($notavail) ) {
    foreach( $notavail as $t )
    {
        $displayname = isset($t["displayname"]) ? $t["displayname"] : '';
        $path = isset($t["path"]) ? $t["path"] : '';
        $type = isset($t["type"]) ? $t["type"] : '';
        $id = isset($t["id"]) ? $t["id"] : '';
        $type_name = isset($doctypes[$type]) ? $doctypes[$type] : 'Unknown';
        echo( "<tr><td class='copy'>$displayname</td><td class='copy'><a target=_blank href='uploadedpdfs/$path'>view</a></td><td class='copy'>$type_name</td><td class='copy' valign='top'><a href='uploaddocuments.php?del=$id'>Del</a></td></tr>" );
    }
}
?>
</table>

<span class="copy">
File: <input type='file' name='newfile'><br>
Type: <select name='newtype'>
<?php 
foreach( $doctypes as $d => $v )
{
    echo( "<option value='$d'>$v</option>" );
}
?>
</select>
<br>
Display Name: <input type='text' name='displayname' class='copy' value=''> <input type='submit' name='addnew' value='Add'>
</span>
</td>
</tr>
</table>

<br><br><br><br><br><br><br>

<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>