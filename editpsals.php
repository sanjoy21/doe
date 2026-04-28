<?php
require_once('mysql.php');
require_once('services.php');

// Initialize variables
// $update = isset($_POST['update']) ? $_POST['update'] : '';
// $resp = isset($_POST['resp']) ? $_POST['resp'] : array();
// $coachemail = isset($_POST['coachemail']) ? $_POST['coachemail'] : array();
// $coachphone = isset($_POST['coachphone']) ? $_POST['coachphone'] : array();
// $returned = isset($_POST['returned']) ? $_POST['returned'] : array();
// $others = isset($_POST['others']) ? $_POST['others'] : array();
// $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if update form was submitted
if( $update ) {
    // Update AED assignments
    if( is_array($resp) ) {
        foreach( $resp as $aedid=>$respid ) {
            $safe_aedid = intval($aedid);
            $safe_respid = mysql_escape_string($respid);
            $safe_email = isset($coachemail[$aedid]) ? mysql_escape_string($coachemail[$aedid]) : '';
            $safe_phone = isset($coachphone[$aedid]) ? mysql_escape_string($coachphone[$aedid]) : '';
            
            db_query( "update aed_esi set psalassignedto = '$safe_respid', psalassignedemail = '$safe_email', psalassignedphone = '$safe_phone' where aedid = $safe_aedid" );
        }
    }
    
    // Handle returned AEDs
    if( is_array($returned) ) {
        foreach( $returned as $aedid=>$throwaway ) {
            $safe_aedid = intval($aedid);
            $safe_respid = isset($resp[$aedid]) ? mysql_escape_string($resp[$aedid]) : '';
            $safe_others = isset($others[$aedid]) ? mysql_escape_string($others[$aedid]) : '';
            
            db_query( "insert into psalhistory ( aedid, assignedto, custom, returntime ) values ( '$safe_aedid', '$safe_respid', '$safe_others', now() )" );
            db_query( "update aed_esi set psalassignedto = '', psalassignedphone = '', psalassignedemail = '' where aedid = $safe_aedid" );
        }
    }
}

// Determine which ID to use
if( $id ) {
    $theid = $id;
} else if( isset($thisusersrow['companyid']) ) {
    $theid = $thisusersrow['companyid'];
} else {
    $theid = 0;
}
?>
<?php include "ssi/top.php"; ?>
<!--start center content-->
<p>

<strong><span class="title">MANAGE <?php echo getSchoolStr( "PSAL AEDs" ); ?></span></strong>
<p>
<form method='post' onSubmit='return checkOK(this)'>
<a target='_blank' href='fieldpsalsheet.php?id=<?php echo $id; ?>'>Field AED Assignment Worksheet</a><br>
<a href='viewcompany.php?id=<?php echo $theid; ?>'>Back to <?php echo getSchoolStr( "School" ); ?></a><br>
<a href='emailpsal.php?id=<?php echo $theid; ?>'>Email All</a><br>
<a href='pdfs/memo_to_coaches.pdf'>Print AED Terms and Conditions</a><br>
<table cellpadding="4" cellspacing="0" border="1" width="100%" class="table3">
    <tr><th>Serial</th><th>Coach</th><th>Email</th><th>Cell Phone</th><th>Ret?</th><th>History</th></tr>
<?php
// Get AED rows
$aed_rows = array();
if( $theid > 0 ) {
    $aed_rows = db_query_rows("select * from aed_esi where clientid=$theid and deleted=0 and location in ( 'PSAL', 'SSAL' ) order by serial");
}

// Get responders
$responder_rows = array();
if( $theid > 0 ) {
    $responder_rows = getResponders( $theid );
}

if( is_array($aed_rows) ) {
    foreach( $aed_rows as $arow ) {
        echo( "<tr>" );
        echo( "<td>" . htmlspecialchars($arow['serial'] ?? '') . "</td>" );
        
        $current_resp = isset($arow["psalassignedto"]) ? htmlspecialchars($arow["psalassignedto"]) : '';
        echo( "<td><input name='resp[" . intval($arow['aedid']) . "]' id='resp" . intval($arow['aedid']) . "' value=\"$current_resp\">" );
        
        $cphone = isset($arow["psalassignedphone"]) ? $arow["psalassignedphone"] : '';
        $cemail = isset($arow["psalassignedemail"]) ? $arow["psalassignedemail"] : '';
        echo( "</td>" );
        
        // Return checkbox
        $ret = "";
        if( isset($arow['psalassignedto']) && $arow['psalassignedto'] ) {
            $ret = "<input type='checkbox' name='returned[" . intval($arow['aedid']) . "]' value='1'>";
        }

        // History
        $history = "";
        $historyarr = db_query_rows( "select * from psalhistory where aedid = " . intval($arow['aedid']) . " order by returntime desc " );
        
        if( is_array($historyarr) ) {
            foreach( $historyarr as $a ) {
                $assigned_name = "";
                if( isset($a['custom']) && $a['custom'] ) {
                    $assigned_name = htmlspecialchars($a['custom']);
                } else if( isset($a['assignedto']) ) {
                    $assigned_name = htmlspecialchars(getAttendeeName( $a['assignedto'] ));
                }
                
                $return_time = isset($a['returntime']) ? getFormattedDateWTime( $a['returntime'] ) : '';
                $history .= "<nobr>" . $assigned_name . ": " . $return_time . "</nobr><br>";
            }
            
            if( $history ) {
                $history_count = count($historyarr);
                $history = "<nobr><a href='#' onClick=\"javascript:toggleDiv( 'histdiv" . intval($arow['aedid']) . "' );return false\" class='copy'>View ($history_count)</a><span id='histdiv" . intval($arow['aedid']) . "href'>></span></nobr> <div id='histdiv" . intval($arow['aedid']) . "' style='display:none'>" . $history . "</div>";
            }
        }

        // Phone and email inputs
        $cphone_input = "";
        $cemail_input = "";
        
        if( isset($arow['psalassignedto']) && $arow['psalassignedto'] ) {
            $cphone_input = "<input type='text' name='coachphone[" . intval($arow['aedid']) . "]' id='coachphone" . intval($arow['aedid']) . "' size='10' value=\"" . htmlspecialchars($cphone) . "\">";
            $cemail_input = "<input type='text' name='coachemail[" . intval($arow['aedid']) . "]' id='coachemail" . intval($arow['aedid']) . "' value=\"" . htmlspecialchars($cemail) . "\">";
        } else {
            $cphone_input = "&nbsp;";
            $cemail_input = "&nbsp;";
        }
        
        echo( "<td>$cemail_input</td><td>$cphone_input</td><td>$ret</td><td>$history</td></tr>" );
    }
}
?>
</table>
<input type='submit' name='update' value='Update'>
</form>
<br><br><br>
<!--end center content-->

<?php include "ssi/footer.php" ; ?>

<script type="text/javascript">
function checkOK( frm ) {
    for( var i = 0; i < frm.elements.length; i++ ) {
        if( frm.elements[i].name && frm.elements[i].name.indexOf( "resp[" ) > -1 ) {
            var aedid = frm.elements[i].id.replace( "resp", "" );
            var phoneField = document.getElementById( "coachphone" + aedid );
            var emailField = document.getElementById( "coachemail" + aedid );
            
            if( phoneField && phoneField.value == "" ) {
                alert( "Phone is required." );
                return false;
            }
            if( emailField && emailField.value == "" ) {
                alert( "Email is required." );
                return false;
            }
        }
    }
    return true;
}

function fillEmail( respid, aedid ) {
    if( respid == "other" ) {
        var othersField = document.getElementById( "others" + aedid );
        if( othersField ) {
            othersField.style.display = "";
        }
        return;
    }
    
    var othersField = document.getElementById( "others" + aedid );
    if( othersField ) {
        othersField.style.display = "none";
    }
    
    var phoneField = document.getElementById( "coachphone" + aedid );
    var emailField = document.getElementById( "coachemail" + aedid );
    
    if( phoneField ) {
        phoneField.value = "";
    }
    if( emailField ) {
        emailField.value = "";
    }
    
    <?php 
    // This section would need to be populated with actual responder data
    // For now, leaving it as a placeholder
    ?>
}

function toggleDiv(element) { 
    var href = document.getElementById(element + "href"); 
    var div = document.getElementById(element);
    
    if( div && href ) {
        if( div.style.display == 'none' ) { 
            div.style.display = 'block'; 
            href.innerHTML = "v"; 
        } else if( div.style.display == 'block' ) { 
            div.style.display = 'none'; 
            href.innerHTML = ">"; 
        } 
    }
}
</script>
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