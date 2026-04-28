<?php
require_once('mysql.php');

if( !isset($yr) || !$yr )
    $yr = date( "Y" );

$whr = "";
if( isset($yr) && $yr > -1 )
    $whr = " where dt like '$yr-%'";

$okavail = db_query_rows( "select * from trainernotes $whr order by dt desc", "dt" );

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    $newnote_safe = isset($newnote) ? $newnote : '';
    $newiscorp_safe = isset($newiscorp) ? $newiscorp : 0;
    db_query( "insert into trainernotes values( '$new_safe', '$newnote_safe', '$newiscorp_safe' )" );
    $okavail = db_query_array( "select * from trainernotes order by dt", "dt", "note" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from trainernotes where dt = '". $del_safe ."' " );
    $okavail = db_query_array( "select * from trainernotes order by dt", "dt", "note" );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">TRAINER NOTES</span></strong>

<p>

<span class="copy">

<table class="table3" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top">
<form method='post' action='trainernotes.php'>
<!--start center content-->
                <a href='trainernotes.php?yr=-1'>View All</a> 
                <?php 
                for( $i = 2008; $i <= date( "Y" ) + 1; $i++ ) {
                    echo( " | <a href='trainernotes.php?yr=$i'>$i</a>" );
                }
                ?>
<table class="table3">
<tr><th class='copy'>Date</th><th class='copy'>Note</th><th class='copy'>Corp ?</th><th class='copy'>Action</th></tr>
<?php 
if( isset($okavail) && is_array($okavail) ) {
    foreach( $okavail as $t=>$trow )
    {
        $note = isset($trow["note"]) ? $trow["note"] : '';
        $iscorp = isset($trow["iscorp"]) ? $trow["iscorp"] : 0;
        echo( "<tr><td class='copy'>$t</td><td class='copy'>$note</td><td class=copy>" . ($iscorp ? "Y" : "N") . "</td><td class='copy' valign='top'><a href='trainernotes.php?del=".$t."'>Del</a></td></tr>" );
    }
}
?>
</table>

<span class="copy">
<input type='text' name='new' class='copy' value=''>(YYYY-mm-dd) Note: <input type='text' name='newnote' class='copy' value=''>
<input type='checkbox' value='1' name='newiscorp'> Corp?
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