<?php
require_once('mysql.php');

// Initialize $opendates if not already set
if( !isset($opendates) )
{
    $opendates = db_query_array( "select * from opendates order by dt", "dt", "dt" );
}

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    db_query( "insert into opendates values( '$new_safe' )" );
    $opendates = db_query_array( "select * from opendates order by dt", "dt", "dt" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from opendates where dt = '". $del_safe ."' " );
    $opendates = db_query_array( "select * from opendates order by dt", "dt", "dt" );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>

<strong><span class="title">*OPEN* DATES</span></strong>
<i>These dates will have no limit on number of classes</i>

<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%"  class="table3">
<tr>
<td valign="top">
<a href='opendates.php?showall=1'>Show All (including past dates)</a> | <a href='opendates.php'>Show Only Future Dates</a>
<form method='post' action='opendates.php'>
<!--start center content-->
<table  class="table3">
<tr><th class='copy'>Date</th></tr>
<?php 
if( isset($opendates) && is_array($opendates) ) {
    foreach( $opendates as $t )
    {
        $dt = strtotime( $t ) ;
        if( !isset($showall) || !$showall )
        {
            if( $dt < time() )
                continue;
        }
        echo( "<tr><td class='copy'>$t</td><td class='copy' valign='top'><a href='opendates.php?del=".$t."'>Del</a></td></tr>" );
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