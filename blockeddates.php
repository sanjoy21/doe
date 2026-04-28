<?php
require_once('mysql.php');

$addnew_safe = $addnew;
$del_safe = $del;
$new_safe = $new;
$newbor_safe = $newbor;
$delb_safe = $delb;
$specialadmin_safe = $specialadmin;
$showall_safe = $showall;

// Initialize $notavail to prevent errors if no action is taken
$notavail = db_query_rows( "select * from blockeddates order by dt" );

if( $addnew_safe )
{
    // Note: $new_safe and $newbor_safe should be sanitized before use in a real application.
    db_query( "insert into blockeddates values( '" . $new_safe . "', '" . $newbor_safe . "' )" );
    // Re-fetch the list after adding
    $notavail = db_query_rows( "select * from blockeddates order by dt" );
}

if( $del_safe )
{
    // Note: $del_safe and $delb_safe should be sanitized before use in a real application.
    db_query( "delete from blockeddates where dt = '". $del_safe ."' and borough = '" . $delb_safe . "' " );
    // Re-fetch the list after deleting
    $notavail = db_query_rows( "select * from blockeddates order by dt" );
}

if( !$specialadmin_safe )
{
    // Redirect if not a special admin
    Header( "location: login.php" );
    exit;
}
?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">BLOCKED DATES</span></strong>

<p>
<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table3">
<tr>
<td valign="top">
<a href='blockeddates.php?showall=1'>Show All (including past dates)</a> | <a href='blockeddates.php'>Show Only Future Dates</a>
<form method='post' action='blockeddates.php'>
<table class="table3">
<tr><th class='copy'>Date</th><th class='copy'>Borough</th></tr>
<?php 
foreach( $notavail as $trow )
{
    // Use quoted array keys
    $t = $trow["dt"];
    $tb = $trow["borough"];

    $dt = strtotime( $t ) ;
    if( !$showall_safe )
    {
        // Skip past dates if not showing all
        if( $dt < time() )
        continue;
    }
    // Safely use array values for display and deletion links
    echo( "<tr><td class='copy'>$t</td><td class='copy'>$tb</td><td class='copy' valign='top'><a href='blockeddates.php?del=" . urlencode($t) . "&delb=" . urlencode($tb) . "'>Del</a></td></tr>" );
}
?>
</table>

<span class="copy">
Date: <input type='text' name='new' class='copy' value=''> (YYYY-mm-dd)<br> Borough: <input type='text' name='newbor' class='copy' value=''><br> <input type='submit' name='addnew' value='Add'>
</span>
</td>
</tr>
</table>

<br><br><br><br><br><br><br>
<?php include "ssi/footer.php" ; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</body>
</html>