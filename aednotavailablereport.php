<?php
include "mysql.php";

function db_escape_string($string) {
    if (is_array($string)) {
        return $string; // Do not escape arrays
    }
    // Basic placeholder for security demonstration
    return str_replace(['\\', "'"], ['\\\\', "\'"], (string)$string);
}

if(isset($go) && $go)
{
    $dtstr = "";
    if(isset($fromdate) && $fromdate)
    {
        $dtstr .= "and dateinupload >= '" . db_escape_string($fromdate) . "' ";
    }
    if(isset($todate) && $todate)
    {
        $dtstr .= "and dateinupload <= '" . db_escape_string($todate) . "' ";
    }
    
    $res = db_query_rows("select * from scuploaddetail sd, scuploaddata sc, appuploads a where a.id = sc.uploadid and sc.id = sd.dataid and sd.name = 'unit_unavailable' and sd.value = 'yes' $dtstr and (archived = 0 or archived = 1) order by serial");
    
    // Generate CSV instead of Excel
    $filename = "aeds_unavailable_" . time() . ".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = [
        "School Name",
        "School Code",
        "School Location",
        "AED Serial Number",
        "AED Location/Floor",
        "Pads A Exp. Date",
        "Pads B Exp. Date",
        "Missing?",
        "Trainer"
    ];
    
    fputcsv($output, $header);
    
    $already = array();
    
    if(isset($res) && is_array($res))
    {
        foreach($res as $row)
        {
            $serial = isset($row['serial']) ? $row['serial'] : '';
            $schoolid = isset($row['schoolid']) ? $row['schoolid'] : '';
            $dateuploaded = isset($row['dateuploaded']) ? $row['dateuploaded'] : '';
            
            if(isset($already[$serial]) && $already[$serial])
            {
                continue;
            }
            
            $already[$serial] = 1;
            
            if($serial && $dateuploaded)
            {
                $donelater = db_query_first_cell("select * from scuploaddetail sd, scuploaddata sc, appuploads a where a.id = sc.uploadid and sc.id = sd.dataid and sd.name = 'unit_unavailable' and sd.value = 'no' $dtstr and (archived = 0 or archived = 1) and serial = '" . db_escape_string($serial) . "' and dateuploaded > '" . db_escape_string($dateuploaded) . "'");
                
                if($donelater)
                {
                    continue;
                }
            }
            
            $aedrow = array();
            $schoolrow = array();
            
            if($serial && $schoolid)
            {
                $aedrow = db_query_first("select * from aed_esi where serial = '" . db_escape_string($serial) . "' and clientid = '" . intval($schoolid) . "' and deleted = 0");
                $schoolrow = db_query_first("select * from company_esi where id = '" . intval($schoolid) . "'");
            }
            
            $schoolname = isset($row['schoolname']) ? $row['schoolname'] : '';
            $schoolcode = isset($schoolrow['schoolcode']) ? $schoolrow['schoolcode'] : '';
            
            // Build school location string
            $address = isset($schoolrow['address']) ? $schoolrow['address'] : '';
            $city = isset($schoolrow['city']) ? $schoolrow['city'] : '';
            $zip = isset($schoolrow['zip']) ? $schoolrow['zip'] : '';
            $schoolLocation = $address;
            if($city)
            {
                $schoolLocation .= $schoolLocation ? ", " . $city : $city;
            }
            if($zip)
            {
                $schoolLocation .= $schoolLocation ? " " . $zip : $zip;
            }
            
            // Build AED location/floor string
            $locationInfo = '';
            if(isset($aedrow['location']))
            {
                $locationInfo = $aedrow['location'];
                if(isset($aedrow['floor']) && $aedrow['floor'])
                {
                    $locationInfo .= "/ " . $aedrow['floor'];
                }
            }
            
            $padaExpiration = isset($aedrow['padaexpiration']) ? $aedrow['padaexpiration'] : '';
            $padbExpiration = isset($aedrow['padbexpiration']) ? $aedrow['padbexpiration'] : '';
            
            $missing = "No";
            if(isset($aedrow['aedmissing']) && $aedrow['aedmissing'])
            {
                $missing = "Yes";
            }
            
            // Build trainer list
            $responder = array();
            if(isset($schoolrow['zip']) && $schoolrow['zip'])
            {
                $responderarr = getTrainersForZip($schoolrow['zip']);
                if(isset($responderarr) && is_array($responderarr))
                {
                    foreach($responderarr as $r)
                    {
                        if(isset($r['name']))
                        {
                            $responder[] = $r['name'];
                        }
                    }
                }
            }
            
            if(isset($schoolrow['id']) && $schoolrow['id'])
            {
                $f = db_query_rows("Select concat(first_name, ' ', last_name) as name from user where extraschools like '%" . db_escape_string($schoolrow['id']) . "%'");
                if(isset($f) && is_array($f))
                {
                    foreach($f as $namearr)
                    {
                        if(isset($namearr['name']))
                        {
                            $responder[] = $namearr['name'];
                        }
                    }
                }
            }
            
            $trainerList = implode(", ", $responder);
            
            // Prepare data row
            $rowData = [
                $schoolname,
                $schoolcode,
                $schoolLocation,
                $serial,
                $locationInfo,
                $padaExpiration,
                $padbExpiration,
                $missing,
                $trainerList
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
    }
    
    fclose($output);
    exit();
}
?>
<?php include "ssi/top.php"; ?>
<form method='post'>
Get Not Available AEDs between:  <?php echo printdates2("fromdate", isset($fromdate) ? $fromdate : ''); ?> and <?php echo printdates2("todate", isset($todate) ? $todate : ''); ?>
<input type='submit' name='go' value='Search'>
</form>

<br><br><br><br><br><br><br>

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