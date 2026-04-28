<?php
$nologinrequired = true;
include "mysql.php";
require_once "services.php";

if( $go && $senddate )
{
    $sql = ( "select class.id from class, company_esi where ( startdate like '{$senddate}%' )  and accepted = 1 and canceldate is null and code not in ( 'MHFA', 'AEDI', 'Inspections', 'MHFA', 'TCF Meeting', 'Esinew', 'Misc', 'Trade', 'Call', 'call', 'Office', 'party', 'misc'  ) and iscorp <> 3 and companyid = company_esi.id and isnational = 0 and companyname not like 'Sample%' and companyname not like 'Open Registration' and numtrainers > 0 " );
    echo( $sql );
    $expiring = db_query_rows( $sql );
    $newemails = array();
    
    foreach( $expiring as $e )
    {
        $id = isset($e['id']) ? $e['id'] : 0;
        // echo( "would send to $id<br>" );
        $num = db_query_first_cell("select count(*) from trainer_to_class where classid = " . intval($id) );
        if( $num ) continue;

        $tmp = requestTrainers( $id, false, false );
        if( is_array($tmp) ) {
            foreach( $tmp as $tid )
            {
                if( $tid ) {
                    $newemails[$tid] = $tid;
                }
            }
            if( count($tmp ) )
                db_query( "update class set lasttrainerreqdate = now() where id = " . intval($id) ); 
        }
    }

    foreach( $newemails as $trainerid )
    {
        // $subject = "Alert! Instructor needed for " . date( "m/d/Y", $threeweeks );
        $subject = "Alert! Instructors needed for upcoming classes!";
        $body = " Instructors needed!
    Click here to view all available instructor opportunities:

https://".SUB_DOE. "." .URL_WITHOUT_SUBDOMAIN."/requesttotrain.php?trainerid=" . ($trainerid*1234) . "\n\n";
        echo( "sent to ".getUserEmail( $trainerid )." <br>" );
        sendMail( getUserEmail( $trainerid ), "$subject", "$body", "barbara@emergencyskills.com", "Scheduling Alert" );
    }
}
?>
<?php include "ssi/top.php"; ?>  

<form method='post'>
Date: <?php echo printdates2( "senddate", isset($senddate) ? $senddate : '' ); ?>
<input type='submit' name='go' value='Send'>
</form>
</span>
<br><br></td></tr>
</td></tr>
</table>
<br><br><br><br><br><br><br>

<!--end center content-->
<?php include "ssi/footer.php"; ?>
<!--end footer-->
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>