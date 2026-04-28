<?php
$nologinrequired = true;
include "mysql.php";
require_once "services.php";

if( getcurrentusercompany() > 0 )
{
    header( "location: login.php" );
    exit;
}
include "ssi/top.php";

$err = '';
if( isset($sendchecked) && $sendchecked )
{
    // Process emails to send
    if( isset($oktosend) && is_array($oktosend) )
    {
        foreach( $oktosend as $counter=>$throwaway )
        {
            $subject = isset($subjects[$counter]) ? stripslashes( $subjects[$counter] ) : '';
            $body = isset($bodies[$counter]) ? nl2br( stripslashes( $bodies[$counter] ) ) : '';
            $fromname = isset($fromnames[$counter]) ? stripslashes( $fromnames[$counter] ) : '';
            $fromemail = isset($fromemails[$counter]) ? stripslashes( $fromemails[$counter] ) : '';
            $tomailto = isset($sendto[$counter]) ? $sendto[$counter] : array();
            $key = isset($keys[$counter]) ? $keys[$counter] : '';
            
            if( is_array($tomailto) )
            {
                foreach( $tomailto as $t )
                {
                    if( $t )
                    {
                        sendHTMLMail( $t, $subject, $body, $fromemail, $fromname, $key );
                    }
                }
            }
            
            sendHTMLMail( "sarahg@emergencyskills.com", $subject, $body, $fromemail, $fromname, $key );
        }
    }
    
    // Process emails not to send
    if( isset($notsending) && is_array($notsending) )
    {
        foreach( $notsending as $counter=>$throwaway )
        {
            $key = isset($keys[$counter]) ? $keys[$counter] : '';
            if( $key )
            {
                db_query( "insert into automatedemails( fromautomated, datesent, torecipients, emailkey ) values ( 0, now(), 'NOT SENDING', '" . addslashes($key) . "' )" );
            }
        }
    }
    
    $err = "<font color='red'>Sent!</font><br>";
}

$counter = 0;
?>
<!--start center content-->

<strong><span class="title">Send Reminder Emails</span></strong>
<p><span class="copy">

    <?php
    echo( isset($err) ? $err : '' );
    
    $enddate_safe = isset($enddate) ? htmlspecialchars($enddate) : '';
    echo("<form method='post'>
View Classes for Month: <input size=8 type='text' name='enddate' value='" . $enddate_safe . "'> (YYYY-MM) <input type='submit' name='go' value='Go'><br>
<table cellspacing=0>" );
echo( "<input type='submit' name='sendchecked' value='Send Checked'>" );

// Process enddate for filtering
$enddate_timestamp = 0;
if( isset($enddate) && $enddate )
{
    $enddate_timestamp = strtotime( $enddate . "-01" );
}
else
{
    $enddate_timestamp = mktime( 0, 0, 0, date("m") + 2, 1, date("Y") - 2 );
}
$lastnight = $enddate_timestamp;

// Query for expiring classes
$sql = "select *, class.id as classid from class, company_esi where startdate like '" . date("Y-m-", $lastnight ) . "%' and class.companyid = company_esi.id and iscorp = 0 and class.deleted = 0 and company_esi.deleted = 0 and showsondrillreports = 1 and companyid <> 2858";
$expiring = db_query_rows( $sql );

$any = false;

if( isset($expiring) && is_array($expiring) )
{
    foreach( $expiring as $e )
    {
        $key = "monrem-class" . (isset($e['classid']) ? $e['classid'] : '') . "_company" . (isset($e['companyid']) ? $e['companyid'] : '');
        
        if( $key && db_query_first_cell( "select id from automatedemails where emailkey = '" . addslashes($key) . "'" ) )
        {
            continue;
        }
        
        $any = true;
        
        $resps = array();
        $companyid_safe = isset($e['companyid']) ? $e['companyid'] : 0;
        if( $companyid_safe )
        {
            $nonexpired = getNonExpiredResponders( $companyid_safe );
            if( isset($nonexpired) && is_array($nonexpired) )
            {
                foreach( $nonexpired as $n )
                {
                    $expiredate = date( "Y-m-d", strtotime( $n['trainingdate'] . "  + 2 years" ) );
                    $resps[] = (isset($n['firstname']) ? $n['firstname'] : '') . " " . (isset($n['lastname']) ? $n['lastname'] : '') . " - " . $expiredate;
                }
            }
        }
        $resps_str = isset($resps) ? implode( "\n", $resps ) : '';
        
        $extratext = "";
        $othersinbuilding = 0;
        if( isset($e['buildingcode']) && $e['buildingcode'] )
        {
            $othersinbuilding = db_query_first_cell( "select count(*) from company_esi where buildingcode = '" . addslashes($e['buildingcode']) . "' and deleted = 0 " );
        }
        if( $othersinbuilding > 1 )
        {
            $extratext = "Additionally, without certified responders, your safety plan will not be certified in the fall.\n\n";
        }
        
        $companyname_safe = isset($e['companyname']) ? $e['companyname'] : '';
        $schoolcode_safe = isset($e['schoolcode']) ? $e['schoolcode'] : '';
        $url_without_subdomain = URL_WITHOUT_SUBDOMAIN;
        
        $body = "This is a courtesy email to inform you all of your AED/CPR responders will be expiring in " . date( "F", $lastnight ) . ".

In order to remain in compliance with Section 917 of the State Education Law, <u>immediate training is recommended</u>.

{$extratext}To schedule training please visit <a href='https://" . SUB_DOE . "." . $url_without_subdomain . "/login.php'>our website</a>.

If you have any questions or concerns please feel free to contact me.

List of current responder certifications for " . $companyname_safe . ":

" . $resps_str . "

If you have already scheduled training, or our data needs updating, please respond and let us know.

Please feel free to contact me with any questions or concerns.

Sarah

Sarah Gillen - Emergency Skills, Inc
Senior Project Manager
NYC Dept. Of Ed. AED Program
Cell: 646-465-3637
ESI: 212-564-6833
DOE: 718-391-8382
http://" . SUB_DOE . "." . $url_without_subdomain . "
";
        
        $fromname = "Sarah Gillen";
        $fromemail = "sarahg@emergencyskills.com";
        $subject = "AED/CPR responder certifications Expiring " . $schoolcode_safe;
        
        $wouldsendto = array();
        if( isset($e['principalemail']) && $e['principalemail'] )
        {
            $wouldsendto[$e['principalemail']] = $e['principalemail'];
        }
        if( isset($e['contactemail']) && $e['contactemail'] )
        {
            $wouldsendto[$e['contactemail']] = $e['contactemail'];
        }
        
        $addedby_email = '';
        if( isset($e['addedby']) && $e['addedby'] )
        {
            $addedby_email = getEmail( $e['addedby'] );
            if( $addedby_email )
            {
                $wouldsendto[$addedby_email] = $addedby_email;
            }
        }
        
        $counter++;
        ?>
<tr class='counter<?php echo $counter; ?>'><td>Class</td><td>
        <?php 
        $classid_safe = isset($e['classid']) ? htmlspecialchars($e['classid']) : '';
        $startdate_safe = isset($e['startdate']) ? htmlspecialchars($e['startdate']) : '';
        if( $classid_safe )
        {
            echo "<a href='viewclass.php?id=" . $classid_safe . "'>#" . $classid_safe . "</a> - " . $startdate_safe;
        }
        ?></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Company</td><td>
        <?php 
        $companyid_safe = isset($e['companyid']) ? htmlspecialchars($e['companyid']) : '';
        $companyname_safe = htmlspecialchars($companyname_safe);
        $bic_safe = (isset($e['bic']) && $e['bic']) ? "Yes" : "No";
        if( $companyid_safe )
        {
            echo "<a href='viewcompany.php?id=" . $companyid_safe . "'>" . $companyname_safe . "</a> BIC: " . $bic_safe;
        }
        ?></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Upcoming Classes</td><td>
        <?php 
        $upcoming = array();
        if( isset($e['companyid']) && $e['companyid'] )
        {
            $upcoming = db_query_rows( "select * from class where companyid = " . intval($e['companyid']) . " and startdate > now() " );
        }
        
        if( isset($upcoming) && is_array($upcoming) )
        {
            foreach( $upcoming as $u )
            {
                if( isset($u['id']) && isset($u['startdate']) )
                {
                    ?>
     <a href='class_detail.php?id=<?php echo htmlspecialchars($u['id']); ?>'>#<?php echo htmlspecialchars($u['id']); ?></a> - <?php echo htmlspecialchars($u['startdate']); ?><br>
                    <?php 
                }
            }
        }
        ?>     </td></tr>
<tr class='counter<?php echo $counter; ?>'><td>From Name:</td><td><input type='text' size='80' name='fromnames[<?php echo $counter; ?>]' value="<?php echo htmlspecialchars($fromname); ?>"></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>From Email:</td><td><input type='text' size='80' name='fromemails[<?php echo $counter; ?>]' value="<?php echo htmlspecialchars($fromemail); ?>"></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Subject:</td><td><input type='text' size='80' name='subjects[<?php echo $counter; ?>]' value="<?php echo htmlspecialchars($subject); ?>"></td></tr>
     <input type='hidden' name='keys[<?php echo $counter; ?>]' value='<?php echo htmlspecialchars($key); ?>'>
        <?php 
        if( isset($wouldsendto) && is_array($wouldsendto) )
        {
            foreach( $wouldsendto as $w )
            {
                if( !$w ) 
                {
                    continue;
                }
                ?>
<tr class='counter<?php echo $counter; ?>'><td>Send To:</td><td><input type='text' size='80' name='sendto[<?php echo $counter; ?>][]' value="<?php echo htmlspecialchars($w); ?>"></td></tr>
                <?php 
            }
        }
        ?>
<tr class='counter<?php echo $counter; ?>'><td>Body:</td><td><textarea cols='90' rows='40'  name='bodies[<?php echo $counter; ?>]'><?php echo htmlspecialchars($body); ?></textarea></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>OK To Send:</td><td><input type='checkbox' id="oktosend<?php echo $counter; ?>" name='oktosend[<?php echo $counter; ?>]' onClick='fixBg( <?php echo $counter; ?> )' value="1"></td></tr>
<tr class='counter<?php echo $counter; ?>'><td><nobr><b>Not</b> Sending:</nobr></td><td><input type='checkbox' id="notsending<?php echo $counter; ?>" name='notsending[<?php echo $counter; ?>]' onClick='fixBg( <?php echo $counter; ?> )' value="1"></td></tr>
<tr class='counter<?php echo $counter; ?>'><td colspan='2'><br><br><br></td></tr>
        <?php
        
        // Process individual responders
        $individuals = array();
        if( isset($e['classid']) && $e['classid'] )
        {
            $individuals = db_query_rows( "select * from responder_to_class, responders_esi where classid=" . intval($e['classid']) . " and responders_esi.responderid = responder_to_class.responderid and individual = 1" );
        }
        
        if( isset($individuals) && is_array($individuals) )
        {
            foreach( $individuals as $i )
            {
                $individual_key = "monrem-class" . (isset($e['classid']) ? $e['classid'] : '') . "_resp" . (isset($i['responderid']) ? $i['responderid'] : '');
                
                if( $individual_key && db_query_first_cell( "select id from automatedemails where emailkey = '" . addslashes($individual_key) . "'" ) )
                {
                    continue;
                }
                
                $clientid_safe = isset($i['clientid']) ? $i['clientid'] : 0;
                $companyname_ind = $clientid_safe ? getCompanyName( $clientid_safe ) : '';
                
                $individual_body = "This is a courtesy email to inform you that your AED/CPR certification will expire in " . date( "F", $lastnight ) . ".
You are listed as a responder at " . $companyname_ind . ". 
To schedule a training program for your school staff, please visit our <a href='https://" . SUB_DOE . "." . $url_without_subdomain . "/login.php'>website.</a>
or
To schedule individual training <a href='http://" . SUB_DOE . ".". $url_without_subdomain . "/individual_registration1.php>click here.</a>

If you have already scheduled training, or our data needs updating, please respond and let us know.
Please feel free to contact me with any questions or concerns.

Sarah

Sarah Gillen - Emergency Skills, Inc
Senior Project Manager
NYC Dept. Of Ed. AED Program
Cell: 646-465-3637
ESI: 212-564-6833
DOE: 718-391-8382
http://" . SUB_DOE . "." . $url_without_subdomain . "
";
                
                $individual_subject = "Your AED/CPR certification is Expiring";
                $individual_wouldsendto = array();
                if( isset($i['email']) && $i['email'] )
                {
                    $individual_wouldsendto[] = $i['email'];
                }
                
                $counter++;
                ?>
     <input type='hidden' name='keys[<?php echo $counter; ?>]' value='<?php echo htmlspecialchars($individual_key); ?>'>
<tr class='counter<?php echo $counter; ?>'><td>Class</td><td><a href='viewclass.php?id=<?php echo $classid_safe; ?>'>#<?php echo $classid_safe; ?></a> - <?php echo $startdate_safe; ?></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Company</td><td><a href='viewcompany.php?id=<?php echo $companyid_safe; ?>'><?php echo $companyname_safe; ?></a> BIC: <?php echo $bic_safe; ?></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Individual</td><td>
                <?php 
                $responderid_safe = isset($i['responderid']) ? htmlspecialchars($i['responderid']) : '';
                $firstname_safe = isset($i['firstname']) ? htmlspecialchars($i['firstname']) : '';
                $lastname_safe = isset($i['lastname']) ? htmlspecialchars($i['lastname']) : '';
                if( $responderid_safe )
                {
                    echo "<a href='viewresponder.php?responderid=" . $responderid_safe . "'>" . $firstname_safe . " " . $lastname_safe . "</a>";
                }
                ?></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Upcoming Classes</td><td>
                <?php 
                $upcoming_individual = array();
                if( isset($e['companyid']) && $e['companyid'] && isset($i['responderid']) && $i['responderid'] )
                {
                    $upcoming_individual = db_query_rows( "select class.* from class, responder_to_class where classid = class.id and companyid = " . intval($e['companyid']) . " and startdate > now() and responderid = " . intval($i['responderid']) );
                }
                
                if( isset($upcoming_individual) && is_array($upcoming_individual) )
                {
                    foreach( $upcoming_individual as $u )
                    {
                        if( isset($u['id']) && isset($u['startdate']) )
                        {
                            ?>
     <a href='class_detail.php?id=<?php echo htmlspecialchars($u['id']); ?>'>#<?php echo htmlspecialchars($u['id']); ?></a> - <?php echo htmlspecialchars($u['startdate']); ?><br>
                            <?php 
                        }
                    }
                }
                ?>     </td></tr>
<tr class='counter<?php echo $counter; ?>'><td>From Name:</td><td><input type='text' size='80' name='fromnames[<?php echo $counter; ?>]' value="<?php echo htmlspecialchars($fromname); ?>"></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>From Email:</td><td><input type='text' size='80' name='fromemails[<?php echo $counter; ?>]' value="<?php echo htmlspecialchars($fromemail); ?>"></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>Subject:</td><td><input type='text' size='80' name='subjects[<?php echo $counter; ?>]' value="<?php echo htmlspecialchars($individual_subject); ?>"></td></tr>
                <?php 
                if( isset($individual_wouldsendto) && is_array($individual_wouldsendto) )
                {
                    foreach( $individual_wouldsendto as $w )
                    {
                        if( !$w ) 
                        {
                            continue;
                        }
                        ?>
<tr class='counter<?php echo $counter; ?>'><td>Send To:</td><td><input type='text' size='80' name='sendto[<?php echo $counter; ?>][]' value="<?php echo htmlspecialchars($w); ?>"></td></tr>
                        <?php 
                    }
                }
                ?>
<tr class='counter<?php echo $counter; ?>'><td>Body:</td><td><textarea cols='90' rows='40'  name='bodies[<?php echo $counter; ?>]'><?php echo htmlspecialchars($individual_body); ?></textarea></td></tr>
<tr class='counter<?php echo $counter; ?>'><td>OK To Send:</td><td><input type='checkbox' id="oktosend<?php echo $counter; ?>" name='oktosend[<?php echo $counter; ?>]' onClick='fixBg( <?php echo $counter; ?> )' value="1"></td></tr>
<tr><td colspan='2'><br><br><br></td></tr>
                <?php
            }
        }
    }
}

echo( "</table>" );
if( !$any ) 
{
    echo( "All reminders already sent. Check <a href='monthlyreminderreport.php'>here.</a><br>" );
}
?>
<input type='submit' name='sendchecked' value='Send Checked'>

</form>
    </span>
<br><br></td></tr>

        </td></tr>
</table>


<br><br><br><br><br><br><br>

<!--end center content-->

                    <?php include "ssi/footer.php" ; ?>

<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
<script language='javascript'>
function showFollow( xls )
{
var fromdate = prompt( "What starting date (mm/dd/yyyy)?" );
var todate = prompt( "What ending date (mm/dd/yyyy)?" );
document.location.href = 'followupdrillreport.php?xls=' + xls + '&concat=true&all=true&fieldfrom=' + fromdate + '&fieldto=' + todate;
}

function fixBg( id )
{
    val = $("#oktosend" + id ).prop( "checked" );
    val2 = $("#notsending" + id ).prop( "checked" ); 
if( val )
    $(".counter" + id ).css( "background-color", "#ffffcc" );
else if( val2 )
    $(".counter" + id ).css( "background-color", "#ffcccc" );
else
    $(".counter" + id ).css( "background-color", "#ffffff" );
}

</script>
</div>
</body>
</html>