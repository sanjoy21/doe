<?php 
include "mysql.php" ;

// Helper function for XSS mitigation
if (!function_exists('h')) {
    function h($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

// NOTE: The SQL query itself is read-only and does not accept external variables, 
// so no SQLi fix is needed here. The focus is on XSS mitigation during output.

$rows = db_query_rows( "select companyname, schoolcode, r.responderid, c.id, cl.id as classid, startdate, r.firstname, r.lastname, r.title from class cl, company_esi c, responders_esi r, responder_to_class rtc left join responder_training_dates rtd  on rtd.responderid = rtc.responderid and rtd.classid = rtc.classid where iscorp = 0 and c.deleted = 0 and r.deleted = 0  and showsondrillreports = 1 and schoolcode like '%R%' and rtc.responderid = r.responderid and c.id = cl.companyid and cl.startdate > '2011-08-20' and cl.startdate < '2012-04-16' and canceldate is null and cl.id = rtc.classid and rtd.trainingdate is null order by classid" );

echo( "<table>" );
foreach( $rows as $r )
{
    // XSS Mitigation: Sanitize all database output using h()
    $classid_safe = h($r['classid']);
    $companyid_safe = h($r['id']);
    
    echo( "<tr>" );
    echo( "<td><a href='class_detail.php?id={$classid_safe}'>" . h($r['startdate']) . "</a></td>" );
    echo( "<td>" . h($r['companyname']) . "</td>" );
    echo( "<td><a href='editcompany.php?id={$companyid_safe}'>" . h($r['schoolcode']) . "</a></td>" );
    echo( "<td>" . h($r['firstname']) . " " . h($r['lastname']) . " (" . h($r['title']) . ")</td>" );
    echo( "</tr>" );
}
echo( "</table>" );

?>