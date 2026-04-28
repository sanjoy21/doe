<?php
require_once('mysql.php');
if( getcurrentusercompany() > 0 )
{
Header( "location: login.php" );
        exit;
}

if( isset($mergechecked) && $mergechecked )
{
    if( isset($tomerge) && is_array($tomerge) ) {
        foreach( $tomerge as $t )
        {
//            echo( "update aed_esi set deleted = 1, deletiondate = Now() where aedid = $t<br>" );
            db_query( "update aed_esi set clientid = 2810 where aedid = $t" );
        }
    }
}

if( isset($deletechecked) && $deletechecked )
{
    if( isset($tomerge) && is_array($tomerge) ) {
        foreach( $tomerge as $t )
        {
//            echo( "update aed_esi set deleted = 1, deletiondate = Now() where aedid = $t<br>" );
            db_query( "update aed_esi set deleted = 1, deletiondate = Now() where aedid = $t" );
        }
    }
}

if( (isset($go) && $go) || (isset($export) && $export) || ( isset($deletechecked) && $deletechecked && isset($fieldvalue) && $fieldvalue )  )
{
    $where = "";
    if( isset($fieldname) && $fieldname && isset($fieldvalue) && $fieldvalue )
    {
        $fieldvalue = trim( $fieldvalue );
        $where .= " and $fieldname like '%$fieldvalue%'";
    }
    if( isset($borough) && $borough )
    {
        $where .= " and borough = '$borough'";
    }
    if( isset($model) && $model )
    {
        $where .= " and model = '$model'";
    }
    if( isset($installedsince) && $installedsince )
    {
        $where .= " and datecompleted >= '".date( "Y-m-d", strtotime( $installedsince ) ). "'";
    }
    
    if( isset($companyid) && $companyid )
    {
        $where .= " and clientid = '$companyid'";
    }
    
    if( !isset($includedeleted) || !$includedeleted )
    {
        $where .= " and r.deleted = 0 ";
    }    

    $sql =  ( "select r.*, c.borough, c.companyname from aed_esi r, company_esi c  where iscorp = '$session_iscorp' and c.id = clientid $where $order" );
//    echo( $sql );
    if( $where )
        $result = db_query_rows( $sql );
    else
    {
        $err = ( "You need to choose a search criteria $fieldname, $fieldvalue, $borough, $companyid..." );
    }
}

if( !isset($export) || !$export )
{
?>
<?php include "ssi/top.php"; ?>
<form name='myform' method='post'><input type='hidden' name='order' value='order by serial'>

<!--start center content-->

<strong><span class="title">AEDS</span></strong>
<p>

<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#999999" class="table3" >
        <tr height="23" bgcolor="#e1e1f6"><td valign="top"><span class="copy"><strong>Aeds:</strong></span></td></tr>
<tr bgcolor="#ffffff"><td valign="bottom"><span class="copy">
        <font color='red'><?php echo isset($err) ? $err : ''?></font><br>
<table cellpadding="3" cellspacing="0" border="0" class="table3" >
            <tr>
            <td valign="middle"><span class="copy">View AEDS </span></td>
<td valign="middle"><span class="copy">By <select style="font-size: 10px;  font-family: verdana;" name='fieldname'>
        <option <?php echo isset($fieldname) && $fieldname=="serial"?"SELECTED":""?> value='serial'>Serial</option>
        <option <?php echo isset($fieldname) && $fieldname=="padaexpiration"?"SELECTED":""?> value='padaexpiration'>Pad A Expiration</option>
        </select>: <input size='10' name='fieldvalue' class=copy value="<?php echo isset($fieldvalue) ? htmlspecialchars($fieldvalue) : ''?>"></span>
<?php 
$model_rows = db_query_rows("select value from esioptionvalues where datatype='model' order by value");
?>
<select name="model" style="font-size: 10px;  font-family: verdana;">
<option value=''>Choose Model</option>
<?php 
                if( isset($model_rows) && is_array($model_rows) ) {
                    foreach ($model_rows as $tmodel) { ?>
                                <option <?php echo isset($model) && isset($tmodel["value"]) && $tmodel["value"]==$model?"SELECTED":""?> value="<?php echo isset($tmodel["value"]) ? $tmodel["value"] : '';?>"><?php echo isset($tmodel["value"]) ? $tmodel["value"] : '';?></option>
                                <?php } 
                }
                ?>
</select>

</td>
<td valign="middle"><input type='submit' class=copy name='go' value='Go'> </td>
            </tr>
<?php if( isOverallAdmin() ) { ?>
<tr><td colspan='3'>Installed Since: <?php echo printdates2( "installedsince", isset($installedsince) ? $installedsince : '' )?> 
&nbsp; &nbsp; &nbsp; &nbsp; Include Retired: <input type='checkbox' name='includedeleted' value=1 <?php echo isset($includedeleted) && $includedeleted?"CHECKED":""?> > </td></tr>
<?php }  ?>
        <tr><td class='copy'><?php echo getSchoolStr( "School" )?>: </td>
        <td colspan='3'>
 <select id="borough" name="borough" onChange="changeBorough();" style="font-size: 10px;  font-family: verdana;">
    <option value=""></option>
<option value="Bronx">The Bronx</option>
<option value="Brooklyn">Brooklyn</option>
<option value="Manhattan">Manhattan</option>
   <option value="Queens">Queens</option>
     <option value="Staten Island">Staten Island</option>
                    </select>
        <?php include "getschooldropdown.php"; ?>

<span class='copy'><?php echo getSchoolStr( "School" )?> Name: </span> <input type='text' id='tmpschoolname' name='tmpschoolname' class='copy' onChange='changeBorough()'> <input type='button' value='Search' class=copy onClick='changeBorough()'>
</td></tr>
<tr>
<td colspan='3' valign="top" ><div id='school_select'>
</div>

</td>
</tr>
</table>
<?php if( isset($result) && $result ) { ?>
<table>
<?php 
if( isset($result) && is_array($result) ) {
    foreach( $result as $row ) { ?>
<tr>
<td class='copy'><a href='editaed.php?aedid=<?php echo isset($row["aedid"]) ? $row["aedid"] : ''?>'><?php echo isset($row["serial"]) ? $row["serial"] : ''?></a></td><td class='copy'><a href='viewcompany.php?id=<?php echo isset($row["clientid"]) ? $row["clientid"] : ''?>'><?php echo isset($row["companyname"]) ? $row["companyname"] : ''?></a></td><td class='copy'><?php echo isset($row["borough"]) ? $row["borough"] : ''?></td></tr>
<?php } 
}
?>
<tr><td colspan='4'><input type='submit' class=copy name='export' value='Export Results'>
</table>
<?php
}
if( isset($go) && $go && isset($result) && !count( $result ) ) {
    ?>
<font color='red'>No results.</font>
    <?php } 

?>

</td></tr></table><!--end center content-->

<?php include "ssi/footer.php" ; ?>

<!--end footer-->
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
<?php } else { 
header("Content-Disposition: attachment; filename=aeds.xls");
header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
echo( "<table>" );
if( isset($result) && is_array($result) ) {
    foreach( $result as $row ) { ?>
<tr>
<td class='copy'><?php echo isset($row["serial"]) ? $row["serial"] : ''?></td><td class='copy'><?php echo isset($row["companyname"]) ? $row["companyname"] : ''?></td><td class='copy'><?php echo isset($row["borough"]) ? $row["borough"] : ''?></td></tr>
<?php } 
}
echo( "</table>" );
} ?>