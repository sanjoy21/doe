<?php
require_once('mysql.php');

if( getcurrentusercompany() > 0 )
{
Header( "location: login.php" );
    exit;
}

if( !isset($xls) || !$xls ){
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">RESPONDERS BY REGION REPORT</span></strong>
<p>
<form method='post' >
    <?php echo isset($err) ? $err : ''; ?>
<form method='get'>
<table>
<tr><td>DOE or Corp: </td><td><select name='doeorcorp'>
     <option value=''>Both</option>
     <option <?php echo (isset($doeorcorp) && strlen($doeorcorp) && !$doeorcorp) ? "SELECTED" : ""; ?> value='0'>DOE</option>
     <option <?php echo (isset($doeorcorp) && strlen($doeorcorp) && $doeorcorp=="1") ? "SELECTED" : ""; ?> value='1'>Corp</option>
     <option <?php echo (isset($doeorcorp) && strlen($doeorcorp) && $doeorcorp=="4") ? "SELECTED" : ""; ?> value='4'>Aging</option>
     </select></td></tr>
<tr><td>Date Range:</td><td> <?php echo printdates2('fromdate', isset($fromdate) ? $fromdate : ''); ?> - <?php echo printdates2('todate', isset($todate) ? $todate : ''); ?></td></tr>
<tr><td>Region (comma separated):</td><td> <input type='text'  name="region" value="<?php echo isset($region) ? $region : ''; ?>"></td></tr>
<tr><td class='copy'><?php echo getSchoolStr("Campus"); ?> ID:</td><td> <select name='campusid' class='copy'>
<option value=''></option>
<?php 
$campuses = db_query_array( "select id, name from campus where iscorp = '$session_iscorp' order by name", "id", "name" );
foreach( $campuses as $tid=>$tname )
{
    echo( "<option ".(isset($campusid) && $tid==$campusid?"SELECTED":"")." value='$tid'>$tname</option>" );
}
?>
</select>
</td></tr>
<tr><td>Company ID:</td><td> <input type='text'  name="companyid" value="<?php echo isset($companyid) ? $companyid : ''; ?>"></td></tr>
<tr><td>CSV Export:</td><td> <input type='checkbox' name='xls' value='1' <?php echo isset($xls) && $xls ? "checked" : ""; ?>></td></tr>
<tr><td> <input type='submit' name='go' value='Go'></td></tr></table>
</form>
<br><br>
<?php
}
else
{
    // Set CSV headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="responders_by_region_' . time() . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
}

if( isset($go) && $go )
{
    if( isset($xls) && $xls )
    {
        // CSV Output
        $output = fopen('php://output', 'w');
        
        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");
        
        // Write header row
        $header = [
            "ID",
            "Last Name",
            "First Name",
            "Title",
            "Email",
            getSchoolStr("School"),
            getSchoolStr("School") . " Code",
            "Most Recent Training Date",
            "Most Recent Class ID"
        ];
        fputcsv($output, $header);
    }
    else
    {
        // HTML Output
        echo( "<table cellspacing=0 border=1>" );
        echo( "<tr><th>ID</th><th>Last Name</th><th>First Name</th><th>Title</th><th>Email</th><th>".getSchoolStr("School")."</th><th>".getSchoolStr("School")." Code</th><th>Most Recent Training Date</th><th>Most Recent Class ID</th></tr>" );
    }
    
    $ext = "";
    if( isset($region) && $region )
    {
        $ext .= " and ( 1 = 0 ";
        $exp = explode( ",", $region );
        foreach( $exp as $e )
        {
            $e = trim( $e );
            $ext .= " or region = '{$e}' ";
        }
        $ext .= " ) ";
    }
    
    if( isset($campusid) && $campusid )
    {
        $ext .= " and campusid = '$campusid'";
    }
    
    if( isset($companyid) && $companyid )
    {
        $ext .= " and co.id = '". $companyid . "' ";
    }
    
    if( isset($doeorcorp) && strlen($doeorcorp) )
    {
        $ext .= " and iscorp= '$doeorcorp'";
    }
    
    if( isset($fromdate) && $fromdate )
    {
        $ext .= " and rtc.trainingdate >= '{$fromdate}'";
    }
    
    if( isset($todate) && $todate )
    {
        $ext .= " and rtc.trainingdate <= '{$todate}'";
    }

    $sql = "select r.pmsid, r.email, classid, rtc.trainingdate, r.firstname, r.title, r.lastname, schoolcode, companyname from responder_training_dates rtc, responders_esi r, company_esi co where co.id = r.clientid $ext and rtc.responderid = r.responderid";
    $res = db_query_rows( $sql );
    
    foreach( $res as $r )
    {
        if( isset($xls) && $xls )
        {
            // CSV row
            $rowData = [
                $r['pmsid'] ?? '',
                $r['lastname'] ?? '',
                $r['firstname'] ?? '',
                $r['title'] ?? '',
                $r['email'] ?? '',
                $r['schoolcode'] ?? '',
                $r['companyname'] ?? '',
                $r['trainingdate'] ?? '',
                $r['classid'] ?? ''
            ];
            
            // Escape any formulas that might start with =, +, - or @ to prevent CSV injection
            foreach($rowData as &$value) {
                if($value !== null && $value !== '') {
                    $firstChar = substr($value, 0, 1);
                    if(in_array($firstChar, array('=', '+', '-', '@'))) {
                        $value = "'" . $value;
                    }
                }
            }
            
            fputcsv($output, $rowData);
        }
        else
        {
            // HTML row
            echo( "<tr>" );
            echo( "<td>" . ($r['pmsid'] ?? '') . "</td>" );
            echo( "<td>" . ($r['lastname'] ?? '') . "</td>" );
            echo( "<td>" . ($r['firstname'] ?? '') . "</td>" );
            echo( "<td>" . ($r['title'] ?? '') . "</td>" );
            echo( "<td>" . ($r['email'] ?? '') . "</td>" );
            echo( "<td>" . ($r['schoolcode'] ?? '') . "</td>" );
            echo( "<td>" . ($r['companyname'] ?? '') . "</td>" );
            echo( "<td>" . ($r['trainingdate'] ?? '') . "</td>" );
            
            echo( "<td><a href='class_detail.php?id=" . ($r['classid'] ?? '') . "' target=_blank>" . ($r['classid'] ?? '') . "</a></td>" );
            
            echo( "</tr>" );
        }
    }
    
    if( isset($xls) && $xls )
    {
        fclose($output);
        exit();
    }
    else
    {
?>
</table>
<?php
    }
}

if( !isset($xls) || !$xls ){
?>
<br><br><br><br><br><br><br>

<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
</div>
</body>
</html>
<?php } ?>