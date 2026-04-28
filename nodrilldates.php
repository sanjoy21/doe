<?php
require_once('mysql.php');

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    db_query( "insert into nodrilldates values( '$new_safe' )" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from nodrilldates where dt = '". $del_safe ."' " );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

$okavail = db_query_array( "select * from nodrilldates order by dt", "dt", "dt" );

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">NO DRILL DATES</span></strong>

<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top">
<form method='post' action='nodrilldates.php'>
<!--start center content-->
<table>
<tr><th class='copy'>Date</th><th class='copy'>Action</th></tr>
<?php 
if( isset($okavail) && is_array($okavail) ) {
    foreach( $okavail as $t )
    {
        echo( "<tr><td class='copy'>$t</td><td class='copy' valign='top'><a href='nodrilldates.php?del=".$t."'>Del</a></td></tr>" );
    }
}
?>
</table>

<span class="copy">
<input type='text' name='new' class='copy' value=''>(YYYY-mm-dd) <input type='submit' name='addnew' value='Add'>
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