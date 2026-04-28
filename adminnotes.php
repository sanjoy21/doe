<?php
require_once('mysql.php');

if( !isset($yr) || !$yr )
    $yr = date( "Y" );

$whr = "";
if( isset($yr) && $yr > -1 )
    $whr = " where dt like '$yr-%'";

$okavail = db_query_rows( "select * from adminnotes $whr order by dt desc", "dt" );

if( isset($addnew) && $addnew )
{
    $new_safe = isset($new) ? $new : '';
    $newnote_safe = isset($newnote) ? $newnote : '';
    $newiscorp_safe = isset($newiscorp) ? $newiscorp : 0;
    $newhex_safe = isset($newhex) ? $newhex : '';
    $endnew_safe = isset($endnew) ? $endnew : '';
    db_query( "insert into adminnotes values( '$new_safe', '$newnote_safe', '$newiscorp_safe', '$newhex_safe', '$endnew_safe' )" );
    $okavail = db_query_array( "select * from adminnotes order by dt", "dt", "note" );
}

if( isset($del) && $del )
{
    $del_safe = $del;
    db_query( "delete from adminnotes where dt = '". $del_safe ."' " );
    $okavail = db_query_array( "select * from adminnotes order by dt", "dt", "note" );
}

if( !isset($specialadmin) || !$specialadmin )
{
    Header( "location: login.php" );
    exit;
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">ADMIN NOTES</span></strong>

<p>

<span class="copy">

<table cellpadding="0" cellspacing="0" border="0" width="100%"  class="table3">
<tr>
<td valign="top">
<form method='post' action='adminnotes.php'>
<!--start center content-->
                <a href='adminnotes.php?yr=-1'>View All</a> 
                <?php 
                for( $i = 2022; $i <= date( "Y" ) + 1; $i++ ) {
                    echo( " | <a href='adminnotes.php?yr=$i'>$i</a>" );
                }
                ?>
<table  class="table3" width='100%' border=1 cellpadding=2 cellspacing=0>
<tr><th class='copy'>Date</th><th class='copy'>Color</th><th class='copy'>Note</th><th class='copy'>Action</th></tr>
<?php 
if( isset($okavail) && is_array($okavail) ) {
    foreach( $okavail as $t=>$trow )
    {
        $note = isset($trow["note"]) ? $trow["note"] : '';
        $bgcolor = isset($trow["bgcolor"]) ? $trow["bgcolor"] : '';
        $enddt = isset($trow["enddt"]) ? $trow["enddt"] : '';
        echo( "<tr><td class='copy'>$t - $enddt</td><td class='copy'>$bgcolor</td><td class='copy'>$note</td><td class='copy' valign='top'><a href='adminnotes.php?del=".$t."'>Del</a></td></tr>" );
    }
}
?>
</table>
<br><br>
<span class="copy">
<table border='1'><tr><td>
<b>Add New</b><br>
Start: <?php echo printdates2( "new" )?> (YYYY-mm-dd)<Br>
End: <?php echo printdates2( "endnew" )?> (YYYY-mm-dd)<Br><br>
Color: <input type='text' name='newhex' size='8' value=''> <a href='https://g.co/kgs/EXC4V5' target='_blank'>Color Picker, copy the "hex" value</a><Br>
<table>
<tr><td bgcolor='#a8bfe6'>#a8bfe6</td></tr>
<tr><td bgcolor='#88f2a4'>#88f2a4</td></tr>
</table>
<Br>Note: <input type='text' name='newnote' class='copy' value=''><br>
<!--<br><input type='checkbox' value='1' name='newiscorp'> Corp?-->
<input type='submit' name='addnew' value='Add'>
</span>
</td>
</tr>
</table>
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