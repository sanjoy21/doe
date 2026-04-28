<?php 
include "mysql.php";
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<tr><td>
<br>
<table cellpadding="8" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" colspan="2">
        <?php
$row = getCompanyRow( $id );
        ?>
<span class='copy'>Class History for : <A href='viewcompany.php?id=<?php echo $id; ?>'><?php echo htmlspecialchars($row["companyname"]); ?></a>:</span><br>
<Table border=0>
<tr><th>Class</th><th>Status</th><th style="max-width: 300px">Training Address(if different)</th><th>Cards Mailed Date</th></tr>
<?php 
$next = db_query_rows( "select id, startdate, code, deleted, cancelreason, cardsmaileddate, accepted, training_location from class where companyid = $id  order by startdate desc" );
$class_names = $allclass_names[$row["iscorp"]];
foreach( $next as $n )
{
    $loc = '';
    if( ($row["iscorp"]) && isset($row["address"]) && isset($n["training_location"]))
    {
        if(strpos($row["address"], $n["training_location"]) === false)
        {
            $loc = $n["training_location"];
        }
    }
    
    $status = '';
    if(isset($n["deleted"]) && $n["deleted"])
    {
        $status = "cancelled - " . $n["cancelreason"];
    }
    elseif(!isset($n["accepted"]) || !$n["accepted"])
    {
        $status = "pending";
    }
    
    $startDate = '';
    if(isset($n["startdate"]))
    {
        $startDate = date("m/d/Y, H:i", strtotime($n["startdate"]));
    }
    
    $className = $class_names[$n["code"]];
    
    echo "<Tr><td><a class='copy' href='class_detail.php?id=" . $n["id"]. "'>" . $startDate . " - " . $className . "</a></td><td>" . $status . "</td><td style=\"max-width: 300px\">" . htmlspecialchars($loc) . "</td><td>" . $n["cardsmaileddate"]  . "</td></tr>";
}
?>
</table>
<br><br>
<?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
</body>
</html>