<br><div align="center">
<table width='100%'><input type='hidden' name='scompletednote' value=''>
<script language='javascript'>
function confirmSComplete( id )
{
var val = prompt( "Please enter any notes here." );
//alert( document.getElementById( "scomplete" + id ).href );
document.getElementById( "scomplete" + id ).href = document.getElementById( "scomplete" + id ).href + "&scompletednote="+escape( val );
return true;
}
</script>
<tr>
<td valign="top"><span class="copy"><strong>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999">
<tr bgcolor="#e1e1f6"><th><span class='copy'>Completed?</th><th><span class='copy'>Date</th><th><span class='copy'>Description</th></tr>
<?php
$row = getCompanyRow( $id );
$phonecalls = db_query_rows("select * from supplyrequests where companyid=$id order by datesent desc");
if( isset($phonecalls) && is_array($phonecalls) ) {
    foreach( $phonecalls as $r )
            {
            ?>
<tr bgcolor='#ffffff'><td> <span class="copy"><?php echo isset($r["completed"]) && $r["completed"] ? "Y" : "N"?>

<?php if( isset($r["completed"]) && !$r["completed"] ){ ?>
<a id="scomplete<?php echo $r["id"]?>" onClick='javascript:return confirmSComplete(<?php echo $r["id"]?>)' href='<?php echo isset($pagename) ? $pagename : ''?>?id=<?php echo $id?>&markscompleted=<?php echo $r["id"]?>'>Complete</a>
<?php } ?>
<?php if( isset($session_userid) && $session_userid == "sarahg@emergencyskills.com" ) { ?>
<a onClick='javascript:return confirm("Are you sure you want to delete this note?" )' href='<?php echo isset($pagename) ? $pagename : ''?>?id=<?php echo $id?>&delsr=<?php echo $r["id"]?>'>Delete</a>
<?php } ?>
</span></td>
<td class='copy'><?php echo isset($r["datesent"]) ? date( "m/d/y h:i a", strtotime( $r["datesent"] ) ) : ''?> <?php echo isset($r["username"]) ? $r["username"] : ''?></td> 
<td class='copy' width='50%'><?php echo isset($r["descr"]) ? $r["descr"] : ''?></td> 
<?php if( isOverallAdmin() ) { ?>
<?php if( isset($r["completed"]) && $r["completed"] ){ ?>
</tr>
<tr><td colspan='4' bgcolor='white' class='copy'>Completed on: <?php echo isset($r["datereplied"]) ? date( "m/d/y h:i a", strtotime( $r["datereplied"] ) ) : ''?>: <?php echo isset($r["completednote"]) ? $r["completednote"] : ''?></td></tr>
<?php } ?>
<tr><td colspan='4'><img src='images/spacer.gif' height='1'></td></tr>
<?php } ?>
</tr>
<?php
}
}
?>
<?php if( isset($currentusertype) && $currentusertype != "trainer" ) { ?>
<tr><td><input class='copy' type='submit' name='addnewsupply' value='Add New'></td><td colspan='2'><textarea name='newsupply' cols='45' rows='3'></textarea></td></tr>
<?php } ?>
</table>
</td>               
</tr>
</table></div><br>