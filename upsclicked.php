<?php
require_once('mysql.php');
require_once "birdie/api.php";

if( getcurrentusertype() != 'principal' )
{
Header( "location: login.php" );
    exit;
}

if( $markreturned )
{
db_query( "update class set equipreturned = 1, markedreturnedby = '$session_id' where id = '$markreturned'" );
}

if( $saveship )
{
foreach( $sn as $id=>$val )
{
db_query( "update class set shipmentstatus = '" . $val . "' where id = $id" );
}
}

if( $birdie )
{
    sendClassesToBirdie( $ids, $type );    
    if( !count( $ids ) )
        $err = "<br><br><font color='red'>Nothing selected</font>";
}

?>
<?php include "ssi/top.php"; ?>
<!--start center content-->



<strong><span class="title">UPS Shipments</span></strong>
    &nbsp;    &nbsp;    &nbsp;    &nbsp;<span><a href='/equipmentstatus.php'>Equipment Status</a></span>
    <?=$err?>
    <form method='post'>
<table width='100%' border=1 cellpadding=2 cellspacing=0  class="table3">
<font  style="padding:3px; background-color:#ffcccc">Within the next 7 days</font> | <font style="padding:3px; background-color:#bc79e0">Class Over</font><br>

<tr><td class="copy"><b>Class Date</b></td><td><b>Class Location</b></td><td><b>Equipment Notes</b></td><td><b>Equipment Shipment Status</b></td><td><b># Masks Ordered</b></td><td><b>Notes</b></td><td><b>Equipment Returned?</b></td></tr>
<?php
//  $days7ago = mktime( 0,0,0, date( "m" ), date( "d" ) - 7 );
//  $days7agostr = date( "Y-m-d", $days7ago );
$sql = "SELECT class.*, companyname FROM class, company_esi WHERE companyid = company_esi.id and class.isups = 1 and startdate> '2017-10-01' and class.deleted = 0 and class.equipreturned = 0 order by startdate ";
//echo( $sql );
$days7 = mktime( 0,0,0, date( "m" ), date( "d" ) + 7 );
$classes = db_query_rows($sql);
foreach( $classes as $c )
{
    $dt = strtotime( $c['startdate'] ?? '' );
    $col = $dt < $days7 ? " bgcolor='#ffcccc' " : "";
    if( $dt < time() ) $col = " bgcolor='#bc79e0' "; 
    echo( "<tr $col><td><a href='class_detail.php?id=".($c['id'] ?? '')."'>".getFormattedDateWTime( $c['startdate'] ?? '' )."</a></td>" );
    echo( "<td><a href='viewcompany.php?id=".($c['companyid'] ?? '')."'>".($c['companyname'] ?? '')."</a></td>" );
    echo( "<td>".($c['equipnotes'] ?? '')."</a></td>" );
    echo( "<td><textarea name='sn[".($c['id'] ?? '')."]' rows=6>".($c['shipmentstatus'] ?? '')."</textarea></a></td>" );
    echo( "<td>".($c['nummasks'] ?? '')."</a></td>" );
    echo( "<td>".($c['notes'] ?? '')."</a></td>" );
    echo( "<td><A href='upsclicked.php?markreturned=".($c['id'] ?? '')."' onClick='return confirm( \"Are you sure you want to mark this as returned?\" )'>Returned</a></td>" );
    echo( "</tr>" );
}
?>
</table>
<input type='submit' name='saveship' value='Save Shipment Statuses'>
</form>
<br><br>
<br><br>
<form method='post' name="seform" id="seform" >
<font  style="padding:3px; background-color:#b2ccf7">DOE</font> | <font style="padding:3px; background-color:#ccffcc">Corp</font><br>
<strong><span class="title">Shipping Export</span></strong>
<table width='100%' border=1 cellpadding=2 cellspacing=0  class="table3">
<tr><td>#</td><td class="copy"><b>ID</td><td class="copy"><b>Class Date</b></td><td><b>Borough</b></td><td><b>Class Location</b></td><td><b>Birdie ID</b></td><td><b>Return Birdie ID</b></td><td><b>Birdie Log</b></td></tr>
<?php 
   $nextmonth = date( "Y-m-d", strtotime( "+ 1 month" ) );
$sql = "SELECT class.*, companyname, iscorp FROM class, company_esi WHERE companyid = company_esi.id and class.isups = 0 and accepted = 1 and startdate> now() and class.deleted = 0 and isconferenceroom = 0 and equipreturned = 0 and startdate < '$nextmonth' order by startdate  ";
//echo( $sql );
$classes = db_query_rows($sql);
$cnt = 0;
foreach( $classes as $c )
{
$hasany = db_query_first_cell( "select count(*) from class_info where classid = '".($c['id'] ?? '')."'" );
    if( !$hasany ) 
        continue;
    $cnt++;
    $col = !empty($c['iscorp']) ? "#ccffcc" : "#d1e2ff";
    echo( "<tr bgcolor='$col'><td>$cnt</td><td><input type='checkbox' name='ids[]' value='".($c['id'] ?? '')."'></td><td><a href='class_detail.php?id=".($c['id'] ?? '')."'>".getFormattedDateWTime( $c['startdate'] ?? '' )."</a></td>" );
    echo( "<td>" . getClassInfoValue( $c['id'] ?? '', "Borough" ). "</td>" );
    echo( "<td><a href='viewcompany.php?id=".($c['companyid'] ?? '')."'>".($c['companyname'] ?? '')."</a></td>" );
    if( getClassInfoValue( $c['id'] ?? '', "Send to Birdie" ) )
    {
        echo( "<td>Sending To Birdie</td>" );
        echo( "<td></td>" );
    }
    else
    {
        if( strtolower( getClassInfoValue( $c['id'] ?? '', "Pick Up Date" ) ) == "jumping" )
        {
            echo( "<td>Jumping</td>" );
        }
        else
        {
            echo( "<td>".($c['birdieid'] ?? '')." (".($c['birdiedatesent'] ?? '').")</td>" );
        }
        if( strtolower( getClassInfoValue( $c['id'] ?? '', "Return Pick Up Date" ) ) == "jumping" )
        {
            echo( "<td>Jumping</td>" );
        }
        else
        {
            echo( "<td>".($c['returnbirdieid'] ?? '')." (".($c['returnbirdiedatesent'] ?? '').")</td>" );
        }
    }
    echo( "<td><a href='viewbirdielog.php?classid=".($c['id'] ?? '')."'>View Log</a></td>" );
    echo( "</tr>" );
}
?>
</table><br>
<?php if( 1 == 0 ) { ?>
Type: <select name='type'>
<option value='both'>Both</option>
<option value='outgoing'>Shipping</option>
<option value='incoming'>Returning</option>
</select>&nbsp;  &nbsp;&nbsp;&nbsp;&nbsp;
<!--    <input type='submit' onClick='document.forms["seform"].action = "shippingexport.php"; return true' name='go' value='Go'>&nbsp;&nbsp;&nbsp;&nbsp;-->
<input type='submit' onClick='document.forms["seform"].action = "upsclicked.php"; return true' name='birdie' value='Export To Birdie'><br><br>
     <?php } ?>
</form>

<br><br><br>
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
<?php include "popupjs.php" ;?>

</html>