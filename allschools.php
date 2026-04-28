<?php 
require_once('mysql.php');

// if( getcurrentusercompany() > 0 && )
// {
// Header( "location: login.php" );
// }

$rowtouse = $thisusersrow;
if( isset($overruserid) && $overruserid )
{
$rowtouse = getUserRow( $overruserid );
}

if( isset($go) && $go )
{
    $zips = "";
    if( isset($rowtouse["usertype"]) && $rowtouse["usertype"] == "trainer" )
    {
        $s = getZips( $rowtouse );
        if( $s )
            $zips = " and zip in ( ".$s." ) ";
    }
    
    if( isset($rowtouse["visibleregion"]) && $rowtouse["visibleregion"] )
        $zips .= " and region in (" . getRegionDisp($rowtouse["visibleregion"]) . ")" ;
        
    if( isset($rowtouse["districts"]) && $rowtouse["districts"] )
        $zips .= getDistrictString( $rowtouse["districts"]);
        
    if( (isset($nodrills) && $nodrills) || (isset($withnodrills) && $withnodrills) )
        $zips .= " and showsondrillreports = 1";

    if( isset($trainerid) && $trainerid )
    {
        $zips .= " and company_esi.zip in (select zip from user_to_zip where userid = $trainerid)" ;
    }

    if( isset($summer) && $summer )
        $zips .= "and summer = 1 ";
        
    if( isset($seventynine) && $seventynine )
        $zips .= "and schoolcode like '79-%' ";
        
    if( isset($noassessments) && $noassessments )
        $zips .= "and ( buildingassessment = '' or buildingassessment is null )";
        
    if( isset($nodrillsneeded) && $nodrillsneeded )
        $zips .= "and ( showsondrillreports = 0 )";

    if( isset($schcode) && $schcode )
    {
        $zips .= " and schoolcode like '$schcode%' ";
    }

    $sql = ( "Select company_esi.*, campus.name as campus from company_esi left join campus on campus.id = campusid where company_esi.iscorp = '$session_iscorp' and deleted = 0 $zips order by borough, region, companyname" );
    //echo( $sql );
    //exit;
    $rep = db_query_rows( $sql );

    if( (isset($nodrills) && $nodrills) || (isset($withnodrills) && $withnodrills) )
    {
        $drillsdontcountbefore = getsetting( 'drillsdontcountbefore' );
        $counter = db_query_array( "Select dtc.companyid, count(distinct( drill.drillid )) as numdrills from drill left join drill_to_companyid dtc on ( drill.drillid = dtc.drillid ) where ( completed =1 or received = 1 or isdone = 1 or shipped = 1 ) and drilldate >= '$drillsdontcountbefore' group by dtc.companyid", "companyid", "numdrills" );
    }

    $trainerzips = db_query_array( "select group_concat( distinct( concat( first_name, ' ', last_name ) )) as name, company_esi.zip  from ( user, company_esi,user_to_zip ) left join territory on trainerid = user.id where user_to_zip.userid = user.id and inactive = 0 and deleted = 0 and (user_to_zip.zip = company_esi.zip ) group by company_esi.zip", "zip", "name" );// left join zip_to_territory on territoryid = territory.id and zip_to_territory.zip ='$zip' 
    
    if( isset($onscreen) && $onscreen )
    {
        include "schoolreport.php";
    }
    else
    {
        include "schoolreportxls.php";
    }
        
    exit;
}
?>
<?php include "ssi/top.php"; ?>
<h3><?php echo getSchoolStr( "Schools" ); ?> Report</h3>
<form method='post'>
Rep: <select name='trainerid'>
<option value=''>All</option>
<?php 
$alltrainers = getAllTrainers();
foreach( $alltrainers as $aname=>$arow ){
    echo( "<option value='$arow[id]'>$arow[first_name] $arow[last_name]</option>" );
}?>
</select>
<br>
<?php 
if( !$session_iscorp && !$thisusersrow["visibleregion"] && !$thisusersrow["districts"] ) { 
?>
Code: <input type='text' name='schcode' size='2'>-X-XXX<br>
<?php } ?>
<input type='checkbox' name='onscreen' value='1'> HTML Version
<input type='submit' name='go' class='copy' value='Get Report'>
</form>
<!--end center content-->

<?php include "ssi/footer.php"; ?>

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