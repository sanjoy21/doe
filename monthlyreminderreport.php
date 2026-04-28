<?php
require_once('mysql.php');

if( getcurrentusercompany() > 0 )
{
    header( "location: login.php" );
    exit;
}

if( isset($sentto) && $sentto )
{
    $sentto = date( "Y-m-d", strtotime( $sentto ) );
}

if( isset($sentfrom) && $sentfrom )
{
    $sentfrom = date( "Y-m-d", strtotime( $sentfrom ) );
}

$err = '';
if( isset($sendagaingo) && $sendagaingo && isset($sendagain) && is_array($sendagain) )
{
    foreach( $sendagain as $emailkey )
    {
        $res = db_query_rows( "select * from automatedemails where datesent < '" . addslashes($sentto) . "' and datesent > '" . addslashes($sentfrom) . "' and emailkey = '" . addslashes($emailkey) . "'" );
        $already = array();
        if( isset($res) && is_array($res) )
        {
            foreach( $res as $r )
            {
                $subject = isset($r['subject']) ? $r['subject'] : '';
                $body = isset($r['body']) ? $r['body'] : '';
                $fromname = isset($r['fromname']) ? $r['fromname'] : '';
                $fromemail = isset($r['fromemail']) ? $r['fromemail'] : '';
                $t = isset($r['torecipients']) ? $r['torecipients'] : '';
                $key = isset($r['emailkey']) ? $r['emailkey'] . "_". $t : '';
                
                if( isset($already[$key]) ) 
                {
                    continue;
                }
                
                $already[$key] = 1;
                sendHTMLMail( $t, $subject, $body, $fromemail, $fromname, $emailkey );
            }
        }
    }
    $err = "<font color='red'>Resent.</font><br><br>";
}
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->

<strong><span class="title">MONTHLY REMINDER REPORT</span></strong>
<p>
<form method='get'>
Emails sent between:  <?php echo printdates2( "sentfrom", isset($sentfrom) ? $sentfrom : '' ); ?> and <?php echo printdates2( "sentto", isset($sentto) ? $sentto : '' ); ?><br>
Include individual sends: <input type='checkbox' name='includeindivid' value='1' <?php echo (isset($includeindivid) && $includeindivid)?"checked":""; ?>>
<input type='submit' name='go' value='Go'>
</form>
<br><br>
<form method='post' action='monthlyreminderresend.php'>
<?php echo isset($err) ? $err : ''; ?>
<?php 

if( isset($go) && $go )
{
    $sentfrom_safe = isset($sentfrom) ? htmlspecialchars($sentfrom) : '';
    $sentto_safe = isset($sentto) ? htmlspecialchars($sentto) : '';
    $includeindivid_safe = isset($includeindivid) ? htmlspecialchars($includeindivid) : '';
    
    echo( "
    <input type='hidden' name='sentfrom' value='" . $sentfrom_safe . "'>
    <input type='hidden' name='sentto' value='" . $sentto_safe . "'>
    <input type='hidden' name='includeindivid' value='" . $includeindivid_safe . "'>
<input type='submit' name='sendagaingo' value='Send Again'>
<table cellspacing=0 border=1><tr><th>ID</th><th>Date Sent</th><!--<th>Email Key</th>--><th>C or I?</th><th>Emails Already Sent</th><th>Originating Class</th><th>School</th><th>Upcoming Classes</th><th>Classes Booked After</th></tr>" );
    
    if( !isset($sentfrom) || !$sentfrom )
    {
        $sentfrom = "2011-01-01";
    }
    
    if( !isset($sentto) || !$sentto )
    {
        $sentto = "2040-01-01";
    }
    
    $ext = "";
    if( isset($companyid) && $companyid )
    {
        $ext .= " and emailkey like '%company" . intval($companyid) . "'";
    }
    
    if( !isset($includeindivid) || !$includeindivid )
    {
        $ext .= " and emailkey like '%company%'";
    }
    
    $res = db_query_rows( "select * from automatedemails where datesent < '" . addslashes($sentto) . " 23:59:59' and datesent > '" . addslashes($sentfrom) . "' and emailkey like 'monrem%' $ext" );
    
    $already = array();
    
    if( isset($res) && is_array($res) )
    {
        foreach( $res as $r )
        {
            if( isset($already[$r['emailkey']]) ) 
            {
                continue;
            }
            
            $already[$r['emailkey']] = 1;
            $emailkey_safe = isset($r['emailkey']) ? htmlspecialchars($r['emailkey']) : '';
            $datesent_safe = isset($r['datesent']) ? htmlspecialchars($r['datesent']) : '';
            
            $classid = 0;
            if( isset($r['emailkey']) && $r['emailkey'] )
            {
                $e = explode( "-", $r['emailkey'] );
                if( isset($e[1]) )
                {
                    $exp = explode( "_", $e[1] );
                    if( isset($exp[0]) )
                    {
                        $classid = intval(str_replace( "class", "", $exp[0] ));
                    }
                }
            }
            
            $alreadysent = array();
            if( isset($r['emailkey']) && $r['emailkey'] )
            {
                $alreadysent = db_query_array( "select count(*) as cnt, date( datesent ) as ds from automatedemails where emailkey = '" . addslashes($r['emailkey']) . "' group by date( datesent ) order by datesent desc", "ds", "cnt" );
            }
            
            $e = array();
            if( $classid )
            {
                $e = db_query_first( "select companyname, class.id as classid, class.* from company_esi, class where class.companyid = company_esi.id and class.id = " . $classid );
            }
            
            echo( "<tr>" );
            echo( "<td><input type='checkbox' name='sendagain[]' value=\"" . $emailkey_safe . "\"></td>" );
            echo( "<td>" . $datesent_safe . "</td>" );
            
            $email_type = "I";
            if( isset($r['emailkey']) && strpos( $r['emailkey'], "company" ) !== false )
            {
                $email_type = "C";
            }
            echo( "<td>" . $email_type . "</td>" );
            
            echo( "<td>" );
            if( isset($alreadysent) && is_array($alreadysent) )
            {
                foreach( $alreadysent as $dt=>$num )
                {
                    echo( "<nobr>" . htmlspecialchars($dt) . "</nobr><br>" );
                }
            }
            echo( "</td>" );
            
            $classid_safe = isset($e['classid']) ? htmlspecialchars($e['classid']) : '';
            $startdate_safe = isset($e['startdate']) ? htmlspecialchars($e['startdate']) : '';
            $companyid_safe = isset($e['companyid']) ? htmlspecialchars($e['companyid']) : '';
            $companyname_safe = isset($e['companyname']) ? htmlspecialchars($e['companyname']) : '';
            
            echo( "<td><a href='viewclass.php?id=" . $classid_safe . "'>#" . $classid_safe . "</a> - " . $startdate_safe . "</td>
<td><a href='viewcompany.php?id=" . $companyid_safe . "'>" . $companyname_safe . "</a> </td>
" );

            $upcoming = array();
            $upcomingstr = "";
            if( isset($e['companyid']) && $e['companyid'] )
            {
                $upcoming = db_query_rows( "select * from class where companyid = " . intval($e['companyid']) . " and startdate > now() " );
                
                if( isset($upcoming) && is_array($upcoming) )
                {
                    foreach( $upcoming as $u )
                    {
                        if( isset($u['id']) && isset($u['startdate']) && isset($u['requestdate']) )
                        {
                            $upcomingstr .= "<tr><td><a href='class_detail.php?id=" . htmlspecialchars($u['id']) . "'>#" . htmlspecialchars($u['id']) . "</a></td><td>" . htmlspecialchars($u['startdate']) . "</td><td>" . htmlspecialchars($u['requestdate']) . "</td></tr>";
                        }
                    }
                }
            }

            if( $upcomingstr )
            {
                echo( "<td><table><tr><th>Class</th><th>Class Date</th><th>Date Scheduled</th></tr>" . $upcomingstr . "</table></td>" );
            }
            else
            {
                echo( "<td></td>" );
            }

            $upcoming = array();
            $upcomingstr = "";
            if( isset($e['companyid']) && $e['companyid'] && isset($r['datesent']) && $r['datesent'] )
            {
                $upcoming = db_query_rows( "select * from class where companyid = " . intval($e['companyid']) . " and requestdate > '" . addslashes($r['datesent']) . "' " );
                
                if( isset($upcoming) && is_array($upcoming) )
                {
                    foreach( $upcoming as $u )
                    {
                        if( isset($u['id']) && isset($u['startdate']) && isset($u['requestdate']) )
                        {
                            $upcomingstr .= "<tr><td><a href='class_detail.php?id=" . htmlspecialchars($u['id']) . "'>#" . htmlspecialchars($u['id']) . "</a></td><td>" . htmlspecialchars($u['startdate']) . "</td><td>" . htmlspecialchars($u['requestdate']) . "</td></tr>";
                        }
                    }
                }
            }

            if( $upcomingstr )
            {
                echo( "<td><table><tr><th>Class</th><th>Class Date</th><th>Date Scheduled</th></tr>" . $upcomingstr . "</table></td>" );
            }
            else
            {
                echo( "<td></td>" );
            }

            echo( "</tr>" );
        }
    }
?>
</table>
<input type='submit' name='sendagaingo' value='Send Again'>
<?php } ?>

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

</script>
</div>
</body>
</html>