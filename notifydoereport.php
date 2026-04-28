<?php 

include "mysql.php";

if( $doit ) { 
    $thetable = "drill";
    $table = "drill";
    $extrafields = ", schoolcode, zip";

    $datefield = "lastnotified";
    $extra = "";
    $swhr = "";

    if( $fieldfrom )
    {
        $tm = fixdate( $fieldfrom ); 
        $extra .= " and $datefield >= '$tm' ";
    }    
    if( $fieldto )
    {
        $tm = fixdate( $fieldto ); 
        $extra .= " and $datefield <= '$tm' ";
    }    

    if( $since )
        $swhr = " and $datefield > '".date( "Y-m-d", strtotime( $since ) )."'";

    // Query for Drill Records
    $sql = "select t.*, companyname, address, city, borough, principalname, contactphone, contactname, contactemail, schoolcode {$extrafields} 
            from company_esi, {$table} t {$lj} 
            where iscorp = '{$session_iscorp}' and companyid = company_esi.id {$swhr} {$extra} 
            order by {$datefield}";

    $res = db_query_rows( $sql );

    // Query for Service Call Records
    $sql = "select t.*, companyname, address, city, borough, principalname, contactphone, contactname, contactemail, schoolcode {$extrafields} 
            from company_esi, servicecall t {$lj} 
            where iscorp = '{$session_iscorp}' and companyid = company_esi.id {$swhr} {$extra} 
            order by {$datefield}";
    $sres = db_query_rows( $sql );

?>
<?php include "ssi/top.php"; ?>        
<!--start center content-->
<table cellpadding="3" cellspacing="0" border="1" width="100%">

<tr><th class='copy'>drill</th><th class='copy'>school</th><th class='copy'>schoolcode</th>
             <th class='copy'>address</th>
             <th class='copy'>telephone number</th>
             <th class='copy'>principal name</th>
             <th class='copy'>last notified date</th>
             <th class='copy'>comments</th>
<?php
foreach( $res as $r )
{
?>
<tr>
<td valign='top' class='copy'><a href='editdrill.php?id=<?php echo $r["drillid"]; ?>'><?php echo $r["drillid"]; ?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?php echo $r["companyid"]; ?>'><?php echo $r["companyname"]; ?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?php echo $r["companyid"]; ?>'><?php echo $r["schoolcode"]; ?></a></td>
<td valign='top' class='copy'><?php echo $r['address']; ?>, <?php echo $r['city']; ?>, <?php echo $r['zip']; ?>, <?php echo $r['borough']; ?></td>
<td valign='top' class='copy'><?php echo $r["contactphone"]; ?></td>
<td valign='top' class='copy'><?php echo $r["principalname"]; ?></td>
<td valign='top' class='copy'><?php echo $r[$datefield]; ?></td>
<td valign='top' class='copy'><?php echo $r["comments"]; ?></td>
</tr>
     <?php } ?>

<tr><th class='copy'>service call</th><th class='copy'>school</th><th class='copy'>schoolcode</th>
             <th class='copy'>address</th>
             <th class='copy'>telephone number</th>
             <th class='copy'>principal name</th>
             <th class='copy'>last notified date</th>
             <th class='copy'>comments</th>
<?php
foreach( $sres as $r )
{
?>
<tr>
<td valign='top' class='copy'><a href='editservicecall.php?id=<?php echo $r["servicecallid"]; ?>'><?php echo $r["servicecallid"]; ?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?php echo $r["companyid"]; ?>'><?php echo $r["companyname"]; ?></a></td>
<td valign='top' class='copy'><a href='viewcompany.php?id=<?php echo $r["companyid"]; ?>'><?php echo $r["schoolcode"]; ?></a></td>
<td valign='top' class='copy'><?php echo $r['address']; ?>, <?php echo $r['city']; ?>, <?php echo $r['zip']; ?>, <?php echo $r['borough']; ?></td>
<td valign='top' class='copy'><?php echo $r["contactphone"]; ?></td>
<td valign='top' class='copy'><?php echo $r["principalname"]; ?></td>
<td valign='top' class='copy'><?php echo $r[$datefield]; ?></td>
<td valign='top' class='copy'><?php echo $r["comments"]; ?></td>
</tr>
     <?php } ?>

     </table>
             
             <br><br><br>
             <!--end center content-->
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
<?php } else { ?>
<?php include "ssi/top.php"; ?>
<form method='post'>
Notify DOE From: <input type='text' name='fieldfrom' size='10' > to <input type='text' name='fieldto' size='10' ><br>
<!--XLS: <input type='checkbox' name='xls' value='1' <?php // echo $xls ? "CHECKED" : ""; ?> ><br>-->
<input type='submit' name='doit' value='Go'>
</form>
<?php include "ssi/footer.php"; ?>
<?php } ?>