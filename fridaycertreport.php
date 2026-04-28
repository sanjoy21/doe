<?php
$nologinrequired = true;
require_once('mysql.php');

// Initialize variables from request
$email = $_GET['email'] ?? null;
$showcalc = $_GET['showcalc'] ?? null;

if (!$email) {
    include "ssi/top.php";
}

// --- 1. Define Reporting Time Window (Previous Friday 12:01am to Thursday Midnight) ---
$midnight_today = mktime(0, 0, 0); // Start of today (e.g., Wednesday)
$midnight = $midnight_today - 1;   // Midnight of yesterday (Thursday 23:59:59)
$midnightstr = date("Y-m-d H:i:s", $midnight);

// Calculate exactly 7 days ago, plus 1 second (Friday 00:00:01)
$oneweekago = strtotime(date("Y-m-d H:i:s", $midnight) . " - 7 days") + 1;
$oneweekagostr = date("Y-m-d H:i:s", $oneweekago);

// echo "End Date: " . $midnightstr . "<br>";
// echo "Start Date: " . $oneweekagostr . "<br>";

$numclasses = [];
$numcards = [];
$numclassescards = [];
$classescardsdates = [];

// --- 2. Fetch Data for DOE (0) and Corporate (1) ---
foreach ([0, 1] as $tmpcorp) {
    // Total Number of Classes Completed in the period
    $numclasses[$tmpcorp] = db_query_first_cell("
        SELECT COUNT(DISTINCT(c.id)) 
        FROM class c
        JOIN company_esi co ON co.id = c.companyid
        JOIN responder_training_dates rtd ON rtd.classid = c.id
        WHERE co.iscorp = {$tmpcorp} 
        AND c.accepted = 1 
        AND rtd.datecompleted > '{$oneweekagostr}' 
        AND rtd.datecompleted < '{$midnightstr}'
    ");

    // Total Number of Cards Sent (Responder count) in the period
    $numcards[$tmpcorp] = db_query_first_cell("
        SELECT COUNT(*) 
        FROM class c
        JOIN company_esi co ON co.id = c.companyid
        JOIN responder_training_dates rtd ON rtd.classid = c.id
        WHERE co.iscorp = {$tmpcorp} 
        AND c.accepted = 1 
        AND rtd.getsecards = 1 
        AND rtd.cardsmaileddate > '{$oneweekagostr}' 
        AND rtd.cardsmaileddate < '{$midnightstr}'
    ");

    // Number of Classes with cards sent (Class count) in the period
    $numclassescards[$tmpcorp] = db_query_first_cell("
        SELECT COUNT(DISTINCT c.id) 
        FROM class c
        JOIN company_esi co ON co.id = c.companyid
        JOIN responder_training_dates rtd ON rtd.classid = c.id
        WHERE co.iscorp = {$tmpcorp} 
        AND c.accepted = 1 
        AND rtd.getsecards = 1 
        AND rtd.cardsmaileddate > '{$oneweekagostr}' 
        AND rtd.cardsmaileddate < '{$midnightstr}'
    ");

    // Detailed rows for calculating time-based percentage
    $classescardsdates[$tmpcorp] = db_query_rows("
        SELECT rtd.cardsmaileddate, c.startdate, c.id AS classid 
        FROM class c
        JOIN company_esi co ON co.id = c.companyid
        JOIN responder_training_dates rtd ON rtd.classid = c.id
        WHERE co.iscorp = {$tmpcorp} 
        AND c.accepted = 1 
        AND rtd.getsecards = 1 
        AND rtd.cardsmaileddate > '{$oneweekagostr}' 
        AND rtd.cardsmaileddate < '{$midnightstr}'
    ");
}

// --- 3. Calculate Time-Based Percentages and Find Failures ---
$doenope = [];
$corpnope = [];
$numdoe = 0;
$numcorp = 0;
$tmpdates = []; // Store human-readable failure details

// DOE (iscorp = 0): Target < 21 days
foreach ($classescardsdates[0] as $tmpsrow) {
    $class_id = $tmpsrow['classid'] ?? 0;
    $cardsmaileddate = $tmpsrow['cardsmaileddate'] ?? '';
    $startdate = $tmpsrow['startdate'] ?? '';

    $tmpc = strtotime($cardsmaileddate);
    $tmps = strtotime($startdate);
    
    // Calculate difference in days (seconds / 60 seconds / 60 minutes / 24 hours)
    $val = ($tmpc - $tmps) / (60 * 60 * 24); 
    
    if ($showcalc) {
        echo "DOE: ({$class_id}) {$startdate}, {$cardsmaileddate}: {$val} days<br>";
    }
    
    if ($val < 21) {
        $numdoe++;
    } else {
        // Class failed the metric
        $doenope[$class_id]++;
        $tmpdates[$class_id] = date("Y-m-d", $tmps) . ", " . $cardsmaileddate . " (" . ceil($val) . " days)";
    }
}

// Corporate (iscorp = 1): Target < 14 days
foreach ($classescardsdates[1] as $tmpsrow) {
    $class_id = $tmpsrow['classid'] ?? 0;
    $cardsmaileddate = $tmpsrow['cardsmaileddate'] ?? '';
    $startdate = $tmpsrow['startdate'] ?? '';

    $tmpc = strtotime($cardsmaileddate);
    $tmps = strtotime($startdate);
    $val = ($tmpc - $tmps) / (60 * 60 * 24);
    
    if ($showcalc) {
        echo "CORP: ({$class_id}) {$startdate}, {$cardsmaileddate}: {$val} days<br>";
    }
    
    if ($val < 14) {
        $numcorp++;
    } else {
        // Class failed the metric
        $corpnope[$class_id]++;
        $tmpdates[$class_id] = date("Y-m-d", $tmps) . ", " . $cardsmaileddate . " (" . ceil($val) . " days)";
    }
}

$total_doe_cards = count($classescardsdates[0]);
$total_corp_cards = count($classescardsdates[1]);

$doepercent = $total_doe_cards > 0 ? number_format($numdoe / $total_doe_cards * 100) : 0;
$corppercent = $total_corp_cards > 0 ? number_format($numcorp / $total_corp_cards * 100) : 0;

// --- 4. Build Report Body (HTML) ---

$body = "
<table border=1 cellpadding=2 cellspacing=0>
<tr><th>Week of " . date("Y-m-d", $oneweekago) . " to " . date("Y-m-d", $midnight) . "</th><th>DOE (iscorp=0)</th><th>Corp (iscorp=1)</th></tr>
<tr><td>Total Number of Classes Completed </td><td>{$numclasses[0]}</td><td>{$numclasses[1]}</td></tr>

<tr><td>Number of Classes with cards sent </td><td>{$numclassescards[0]}</td><td>{$numclassescards[1]}</td></tr>

<tr><td>% of Corporate Cards sent within 14 days </td><td>&nbsp;</td><td>{$corppercent}%</td></tr>

<tr><td>% of DOE Cards sent within 21 days </td><td>{$doepercent}%</td><td></td></tr>

<tr><td>Total Number of Cards Sent</td><td>{$numcards[0]}</td><td>{$numcards[1]}</td></tr>
</table>
";

// --- 5. List Failures ---

$body .= "<br><br><b>DOE Not Sent within 21 days:</b><br>\n";

foreach ($doenope as $notsent => $numnotsent) {
    // Fetch company name and responder count for failed classes
    $crow = db_query_first("
        SELECT co.companyname, 
               (SELECT COUNT(*) FROM responder_to_class WHERE classid = c.id) AS numresponders 
        FROM company_esi co
        JOIN class c ON c.companyid = co.id
        WHERE c.id = '{$notsent}'
    ");
    
    $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
    $body .= "<a href='http://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/viewclass.php?id={$notsent}'>{$notsent} - {$company_name} - ({$tmpdates[$notsent]})</a><br>\n";
}

$body .= "<br><br><b>Corp Not Sent within 14 days:</b><br>\n";

foreach ($corpnope as $notsent => $numnotsent) {
    $crow = db_query_first("
        SELECT co.companyname, 
               (SELECT COUNT(*) FROM responder_to_class WHERE classid = c.id) AS numresponders 
        FROM company_esi co
        JOIN class c ON c.companyid = co.id
        WHERE c.id = '{$notsent}'
    ");
    
    $company_name = htmlspecialchars($crow['companyname'] ?? 'N/A');
    // Note the client-specific URL prefix for corporate clients
    $body .= "<a href='http://". SUB_DOE ."." . URL_WITHOUT_SUBDOMAIN . "/viewclass.php?id={$notsent}'>{$notsent} - {$company_name} - ({$tmpdates[$notsent]})</a><br>\n";
}

$subject = "Weekly Card Processing Report";

// --- 6. Email or Display ---
if ($email) {
    // Assumed external function for sending HTML email
    sendHTMLMail("sarahg@emergencyskills.com", $subject, $body, "info@emergencyskills.com");
    sendHTMLMail("barbara@emergencyskills.com", $subject, $body, "info@emergencyskills.com");
    sendHTMLMail("tcadmin@emergencyskills.com", $subject, $body, "info@emergencyskills.com");
}

if (!$email) {
    echo " <p> ";
    echo $body;
?>
 </span>
 <br><br></td></tr>

 </td></tr>
 </table>

<br><br><br><br><br><br><br>

<?php include "ssi/footer.php" ; ?>
 </span>
 </td>
 <td valign="top" width="15"><img src="images/dotclear.gif" width="10" alt=""></td>
 </tr>
</table>
<br><br>
</div>
</body>
</html>

 <?php
}
?>