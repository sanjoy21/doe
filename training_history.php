<?php
include "mysql.php"; 

if( isset($id) && $id )
{
    $trainer_row = getUserRow( $id );
}
else
{
    $trainer_row = array();
}

$noleftnav = 1;

include "ssi/top.php";

$whr = "";
if( isset($hideservicecalls) && $hideservicecalls )
{
    $whr .= " and companyname <> 'Service Calls'";
}

$classes = array();
if( isset($id) && $id )
{
    $classes = db_query_rows( "select c.*, company_esi.companyname from trainer_to_class ttc, class c, company_esi where c.id = classid and ttc.trainerid = " . intval($id) . " and companyid = company_esi.id $whr order by startdate desc" );
}
?>

</head>
<h3>Training History for <?php 
if( isset($id) && $id && isset($trainer_row['first_name']) && isset($trainer_row['last_name']) )
{
    echo  "<a href='trainer_view.php?tid=" . htmlspecialchars($id) . "'>" . htmlspecialchars($trainer_row['first_name']) . " " . htmlspecialchars($trainer_row['last_name']) . "</a>";
}
else
{
    echo "Unknown Trainer";
}
?></h3>
<br>
<?php if( isset($id) && $id ) { ?>
<a href='training_history.php?id=<?php echo htmlspecialchars($id); ?>&hideservicecalls=1'>Hide Service Calls</a> || <a href='training_history.php?id=<?php echo htmlspecialchars($id); ?>'>Show All</a> 
<?php } ?>
<br><br>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<?php 
            if( isset($classes) && is_array($classes) )
            {
                foreach( $classes as $crow ) { 
                    if( isset($crow['id']) && isset($crow['startdate']) && isset($crow['companyname']) )
                    {
?>
<tr>
<td><a href='class_detail.php?id=<?php echo htmlspecialchars($crow['id']); ?>'>#<?php echo htmlspecialchars($crow['id']); ?></a></td>
<td><?php echo htmlspecialchars($crow['startdate']); ?></td>
<td><?php echo htmlspecialchars($crow['companyname']); ?></td>
</tr>
<?php 
                    }
                } 
            }
?>

</table>
</td></tr></table>
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