<?php
require_once('mysql.php');

// Initialize $okavail if not already set
if( !isset($okavail) )
{
    $okavail = db_query_array( "select * from okdates order by dt", "dt", "dt" );
}

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    db_query( "insert into okdates values( '$new_safe' )" );
    $okavail = db_query_array( "select * from okdates order by dt", "dt", "dt" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from okdates where dt = '". $del_safe ."' " );
    $okavail = db_query_array( "select * from okdates order by dt", "dt", "dt" );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">OK DATES</span></strong>

<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%"  class="table3">
<tr>
<td valign="top">
<form method='post' action='okdates.php'>
<!--start center content-->
<table  class="table3">
<tr><th class='copy'>Date</th></tr>
<?php 
if( isset($okavail) && is_array($okavail) ) {
    foreach( $okavail as $t )
    {
        echo( "<tr><td class='copy'>$t</td><td class='copy' valign='top'><a href='okdates.php?del=".$t."'>Del</a></td></tr>" );
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