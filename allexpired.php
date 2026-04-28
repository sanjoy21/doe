<?php

include "mysql.php";

$schools = getExpiredSchools( $minnum, getRegionDisp($thisusersrow["visibleregion"]) );

// --- CSV Output Section ---
if( $xls )
{
    $filename = "expired".time().".csv";
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write BOM for UTF-8 to handle special characters in Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write header row
    $header = array(
        "School Code",
        "School Name",
        "Region",
        "BIC",
        "Principal Name",
        "Principal Email",
        "Num Responders Trained",
        "Last Expiration Date",
        "Expiration Date With Extension",
        "Next Training Date",
        "Notes"
    );
    fputcsv($output, $header);
    
    foreach( $schools as $sid => $srow )
    {
        $crow = $srow;
        
        // Format dates
        $maxdate_formatted = $srow['maxdate'] ? date("m/d/Y", strtotime($srow['maxdate'])) : "";
        $extension_date = $srow['maxdate'] ? getExtensionDate($srow['maxdate']) : "";
        
        // Find next scheduled training class
        $sql = "select id, startdate from class where companyid = {$sid} and startdate > now() and accepted = 1 and deleted = 0 order by startdate limit 1";
        $classdata = db_query_first($sql);
        $sd = $classdata ? getFormattedDateWTime($classdata['startdate']) : "";
        
        // Prepare data row
        $rowData = array(
            $crow['schoolcode'],
            $crow['companyname'],
            $crow['region'],
            $crow['bic'],
            $crow['principalname'],
            $crow['principalemail'],
            $srow['numdates'],
            $maxdate_formatted,
            $extension_date,
            $sd,
            $crow['companynotes']
        );
        
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
    
    fclose($output);
    exit();
}
// --- HTML Output Section ---
else
{
    $i = 1;
    echo( "<table border=1>" );
    
    if( !$minnum )
        $minnum = "0";
        
    echo( "<tr><td></td><td>School Code</td><td>School Name</td><td>Region</td><td>BIC</td><td>Principal Name</td><td>Principal Email</td>" );
    echo( "<td>Num Responders Trained</td><td>Latest Date</td><td>Expiration Date With Extension</td>" );
    echo( "<td>Next Training Date</td><td>Last Drill Date</td><td>Last Drill Comments</td><td>Other Responders In Building</td><td>Notes</td></tr>" );
    
    foreach( $schools as $sid => $srow )
    {
        // Use quoted array keys
        $crow = getCompanyRow($sid);
        $sids = array();
        
        // This conditional logic (1 == 0) suggests the campus logic is disabled or experimental
        if( $crow["campusid"] && 1 == 0)
        {
            $others = getSchoolsInCampus( $crow["campusid"], $sid );
            foreach( $others as $o )
            {
                // Use quoted array keys
                $sids[$o['id']] = $o['companyname'];
            }
        }

        echo( "<tr>" );
        // Ensure all array keys are quoted
        echo( "<td><a href='viewcompany.php?id={$sid}'>{$sid}</a></td><td><a href='viewcompany.php?id={$sid}'>{$crow['schoolcode']}&nbsp;</a></td>" );
        
        // Find next scheduled training class
        $sql = "select id, startdate from class where companyid = {$sid} and startdate > now() and accepted = 1 and deleted = 0 order by startdate";
        $classdata = db_query_first($sql);
        $sd = $classdata ? getFormattedDateWTime( $classdata['startdate'] ) : "";
        
        // Find last drill data
        $lastdrill_sql = "select drill.* from drill, drill_to_companyid dtc where dtc.drillid = drill.drillid and dtc.companyid = '{$crow['id']}' order by drilldate limit 1";
        $lastdrill = db_query_first($lastdrill_sql);
        
        $dd = $lastdrill['drilldate'];
        $dc = $lastdrill['comments'];
        
        $resps = "";
        foreach( $sids as $s => $sname )
        {
            $responder_rows = db_query_rows("select clientid, responderid, firstname, lastname from responders_esi where clientid = {$s} and deleted=0 order by lastname");
            $count = 0;
            foreach( $responder_rows as $r )
            {
                // Use quoted array keys
                $mostcurrent = db_query_first( "Select responder_training_dates.*, class.code from responder_training_dates left join class on classid = class.id where responderid = {$r['responderid']} order by trainingdate desc" );
                
                // 2 years in seconds (assuming 365 days/year)
                $twoyears = 24*60*365*2*60; 
                
                // Use quoted array keys
                $thedt = strtotime( $mostcurrent['trainingdate']) + $twoyears;
                
                if( $thedt >= time() )
                    $count++;
            }
            $resps .= $sname . ": " . $count . "<br>";
        }
        
        // Ensure all array keys are quoted
        echo( "<td>{$crow['companyname']}</td>" );
        echo( "<td>{$crow['region']}</td>" );
        echo( "<td>{$crow['bic']}</td>" );
        echo( "<td>{$crow['principalname']}</td>" );
        echo( "<td>{$crow['principalemail']}</td>" );
        
        // Use quoted array keys
        $maxdate_formatted = $srow['maxdate'] ? date( "m/d/Y", strtotime($srow['maxdate'])) : "";
        $extension_date = $srow['maxdate'] ? getExtensionDate( $srow['maxdate']) : "";

        echo( "<td>{$srow['numdates']}</td><td>{$maxdate_formatted}</td>" );
        echo( "<td>{$extension_date}</td>" );
        
        // Use quoted array keys
        echo( "<td>{$sd}</td><td>{$dd}</td><td>{$dc}</td><td>{$resps}</td><td>{$crow['companynotes']}</td></tr>" );
        $i++;
    } 
    ?>
    </table>
    <?php 
} 
?>