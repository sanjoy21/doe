<?php
require_once('mysql.php');

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    $newurl_safe = isset($newurl) ? $newurl : '';
    db_query( "insert into dashboardreports ( name, url ) values( '$new_safe', '$newurl_safe' )" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from dashboardreports where id = '". $del_safe ."' " );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">Dashboard Reports</span></strong>

<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%"  class="table3">
<tr>
<td valign="top">
<form method='post' >
<!--start center content-->
<table  class="table3">
<tr><th class='copy'>Name</th><th class='copy'>URL</th><th class='copy'>Action</th></tr>
<?php 
$notavail = db_query_rows( "select * from dashboardreports order by id" );
if( isset($notavail) && is_array($notavail) ) {
    foreach( $notavail as $t )
    {
        $name = isset($t["name"]) ? $t["name"] : '';
        $url = isset($t["url"]) ? $t["url"] : '';
        $id = isset($t["id"]) ? $t["id"] : '';
        echo( "<tr><td class='copy'>$name</td><td class='copy'>$url</td><td class='copy' valign='top'><a href='reportlists.php?del=$id'>Del</a></td></tr>" );
    }
}
?>
</table>

<span class="copy">
New Name: <input type='text' name='new' class='copy' value=''><br>
New URL: <input type='text' name='newurl' class='copy' value=''> (no http:// part, please)<br>
<input type='submit' name='addnew' value='Add'>
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