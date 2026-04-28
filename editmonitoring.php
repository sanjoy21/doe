<?php
include "mysql.php"; 

if( isset($delete) && $delete && isset($monitoringid) && $monitoringid )
{
    db_query( "delete from monitoring where monitoringid = " . intval($monitoringid) );
    if( isset($redirect) && $redirect )
    {
        header( "location: " . $redirect );
        exit;
    }
}

if( isset($update) && $update )
{
    if( isset($monitoringdate) && $monitoringdate )
    {
        $monitoringdate = fixdate( $monitoringdate );
    }
    
    if( isset($monitoringid) && $monitoringid )
    {
        $oldscrow = db_query_first( "select * from monitoring where monitoringid = " . intval($monitoringid) );
        $thecid = isset($oldscrow['companyid']) ? $oldscrow['companyid'] : '';
        
        $iswrittentest_safe = isset($iswrittentest) ? $iswrittentest : '';
        $formlink_safe = isset($formlink) ? $formlink : '';
        $isskillstest_safe = isset($isskillstest) ? $isskillstest : '';
        $writtentestdate_safe = isset($writtentestdate) ? $writtentestdate : '';
        $skillstestdate_safe = isset($skillstestdate) ? $skillstestdate : '';
        $tcfaculty_safe = isset($tcfaculty) ? $tcfaculty : '';
        $comments_safe = isset($comments) ? $comments : '';
        $needsremediation_safe = isset($needsremediation) ? $needsremediation : '';
        $monitoringdate_safe = isset($monitoringdate) ? $monitoringdate : '';
        $nextmonitoringdate_safe = isset($nextmonitoringdate) ? $nextmonitoringdate : '';
        
        db_query( "update monitoring set iswrittentest = '$iswrittentest_safe', formlink = '$formlink_safe', isskillstest = '$isskillstest_safe', writtentestdate = '$writtentestdate_safe', skillstestdate = '$skillstestdate_safe', tcfaculty = '$tcfaculty_safe', comments = '$comments_safe', needsremediation = '$needsremediation_safe', monitoringdate = '$monitoringdate_safe', nextmonitoringdate = '$nextmonitoringdate_safe' where monitoringid = " . intval($monitoringid) );
    }
    else
    {
        $thecid = isset($id) ? $id : '';
        
        $tcfaculty_safe = isset($tcfaculty) ? $tcfaculty : '';
        $comments_safe = isset($comments) ? $comments : '';
        $id_safe = isset($id) ? intval($id) : 0;
        $monitoringdate_safe = isset($monitoringdate) ? $monitoringdate : '';
        $nextmonitoringdate_safe = isset($nextmonitoringdate) ? $nextmonitoringdate : '';
        $needsremediation_safe = isset($needsremediation) ? $needsremediation : '';
        $iswrittentest_safe = isset($iswrittentest) ? $iswrittentest : '';
        $isskillstest_safe = isset($isskillstest) ? $isskillstest : '';
        $writtentestdate_safe = isset($writtentestdate) ? $writtentestdate : '';
        $skillstestdate_safe = isset($skillstestdate) ? $skillstestdate : '';
        $formlink_safe = isset($formlink) ? $formlink : '';
        
        $monitoringid = db_query_insert_id( "insert into monitoring ( tcfaculty, comments, trainerid, monitoringdate, nextmonitoringdate, needsremediation, iswrittentest, isskillstest, writtentestdate, skillstestdate, formlink ) values ('$tcfaculty_safe', '$comments_safe', '$id_safe','$monitoringdate_safe','$nextmonitoringdate_safe','$needsremediation_safe', '$iswrittentest_safe', '$isskillstest_safe', '$writtentestdate_safe', '$skillstestdate_safe', '$formlink_safe') " );
    }

    if( isset($redirect) && $redirect )
    {
        header( "location: " . $redirect );
        exit;
    }
}

//get info for the form
if( isset($monitoringid) && $monitoringid )
{
    $monitoring_row = db_query_first( "select * from monitoring where monitoringid = " . intval($monitoringid) );
    $id = isset($monitoring_row["trainerid"]) ? $monitoring_row["trainerid"] : '';
}

if( !isset($monitoring_row) || !$monitoring_row )
{
    $monitoring_row = array();
}

if( isset($id) && $id )
{
    $trainer_row = getUserRow( $id );
}
else
{
    $trainer_row = array();
}

$noleftnav = 1;

include "ssi/top.php";
include "getschooldropdown.php"; 

?>
<script LANGUAGE="JavaScript">
<!--
function confirmDelete()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree) {
return true ;
}
else
{
return false ;
}
}
// -->
</script>

</head>

<script LANGUAGE="JavaScript1.1" SRC="/FormChek.js"></script>
<script language="JavaScript">

function validRequired(formField,fieldLabel)
{
var result = true;

if (formField.value == "")
{
alert('Please enter a value for the "' + fieldLabel +'" field.');
formField.focus();
result = false;
}

return result;
}

function allDigits(str)
{
return inValidCharSet(str,"0123456789");
}

function inValidCharSet(str,charset)
{
var result = true;

for (var i=0;i<str.length;i++)
if (charset.indexOf(str.substr(i,1))<0)
{
result = false;
break;
}

return result;
}

function isValidShortDate(formField,fieldLabel,required)
{
    if (required && (formField.value.length>7))
    {
        alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
formField.focus();
return false;
    }
var result = true;
var formValue = formField.value;

if (required && !validRequired(formField,fieldLabel))
result = false;

if (result && (formField.value.length>0))
{
var elems = formValue.split("/");

result = (elems.length == 2); // should be two components
var expired = false;

if (result)
{
var month = parseInt(elems[0],10);
var year = parseInt(elems[1],10);

if (elems[1].length == 2)
year += 2000;

var now = new Date();

var nowMonth = now.getMonth() + 1;
var nowYear = now.getFullYear();

result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
 allDigits(elems[1]) && ((elems[1].length == 2) || (elems[1].length == 4));
 }
 
  if (!result)
 {
 alert('Please enter a date in the format MM/YYYY for the "' + fieldLabel +'" field.');
formField.focus();
}
} 
return result;
}
</script>

<?php
if( !isset($redirect) || !$redirect )
{
    $redirect = "/trainer_view.php?tid=" . (isset($id) ? $id : '');
}
?>

<form method="post" enctype='multipart/form-data'>
<input type="hidden" name ="redirect" value="<?php echo isset($redirect) ? htmlspecialchars($redirect) : ''; ?>">
<input type="hidden" name ="update" value="true">
<input type="hidden" name ="monitoringid" value="<?php echo isset($monitoringid) ? htmlspecialchars($monitoringid) : ''; ?>">
<input type="hidden" name ="id" value="<?php echo isset($id) ? htmlspecialchars($id) : ''; ?>">
<?php if( isset($specialadmin) && $specialadmin ) { ?>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" align="right" bgcolor="#ffffff" colspan="2"><span class="small"><strong><a href="trainers.php">&laquo; Back to Admin Main</a></strong></span></td>
</tr>
</table>
<?php } ?>
<strong>THIS MONITORING IS FOR:</strong><br>
<?php 
        if( isset($id) && $id && isset($trainer_row['first_name']) && isset($trainer_row['last_name']) )
        {
            echo  "<a href='trainer_view.php?tid=" . htmlspecialchars($id) . "'>" . htmlspecialchars($trainer_row['first_name']) . " " . htmlspecialchars($trainer_row['last_name']) . "</a>";
        }
        ?>
<br><br>
<table cellpadding="5" cellspacing="1" border="0" width="100%">
<tr>
<td valign="top" bgcolor="#5a179e" colspan="2"><span class="white"><Strong>Monitoring Information</strong></span></td>
</tr>


<tr>
<td valign="top" bgcolor="#E2DFDF">
<table>
    <tr><td>    <span class="copy"><strong>Monitoring Date*:</strong></span> </td><td><?php 
    $monitoringdate_value = '';
    if( isset($monitoring_row['monitoringid']) && $monitoring_row['monitoringid'] && isset($monitoring_row['monitoringdate']) )
    {
        $monitoringdate_value = $monitoring_row['monitoringdate'];
    }
    echo printdates2( "monitoringdate", $monitoringdate_value ); ?> <strong></strong> </span>
    </td></tr>
    <?php
    // $twoyears = date( "Y-m-d", strtotime( "today + 1 year" ) );
    ?>
    <tr><td><strong>Next Monitoring Date:</strong></td><td> <?php 
            $nextmonitoringdate_value = '';
            if( isset($monitoring_row['nextmonitoringdate']) && $monitoring_row['nextmonitoringdate'] )
            {
                $nextmonitoringdate_value = $monitoring_row['nextmonitoringdate'];
            }
            echo printdates2( "nextmonitoringdate", $nextmonitoringdate_value, false, "nextone" ); ?>
</td></tr>
<tr><td><strong>TC Faculty:</strong></td><td><select name='tcfaculty'>
    <?php if( isset($monitoring_row["tcfaculty"]) && $monitoring_row["tcfaculty"] ) { ?>
                                            <option value='<?php echo htmlspecialchars($monitoring_row["tcfaculty"]); ?>'><?php echo htmlspecialchars($monitoring_row["tcfaculty"]); ?></option>
                                            <?php } ?>
    <option value=''>Choose</option>
    <?php 
    $tcfac = db_query_rows( "select * from user where tcfaculty = 1" );
    if( isset($tcfac) && is_array($tcfac) )
    {
        foreach( $tcfac as $frow ) { 
            if( isset($frow['first_name']) && isset($frow['last_name']) )
            {
    ?>
    <option value='<?php echo htmlspecialchars($frow['first_name'] . " " . $frow['last_name']); ?>'><?php echo htmlspecialchars($frow['first_name'] . " " . $frow['last_name']); ?></option>
    <?php 
            }
        }
    }
    ?>
    </select>
</td></tr>
<tr><td><strong>Status:</strong></td><td> 
    <input type='radio' name='needsremediation' <?php echo (isset($monitoring_row['needsremediation']) && $monitoring_row['needsremediation']==1)?"CHECKED":""; ?> value='1' > Needs Remediation &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type='radio' name='needsremediation' value='-1' <?php echo (isset($monitoring_row['needsremediation']) && $monitoring_row['needsremediation'] == -1)?"CHECKED":""; ?>  > Satisfactory
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type='radio' name='needsremediation' value='0' <?php echo (!isset($monitoring_row['needsremediation']) || !$monitoring_row['needsremediation']) ?"CHECKED":""; ?>  > Not Set
</td></tr>
<tr><td><strong>Comments:</strong></td><td> <textarea cols='80' rows='4' name='comments'><?php echo isset($monitoring_row['comments']) ? htmlspecialchars($monitoring_row['comments']) : ''; ?></textarea><br><br></td></tr>
<tr><td><strong>Form Link:</strong></td><td><input type='text' name='formlink' value='<?php echo isset($monitoring_row['formlink']) ? htmlspecialchars($monitoring_row['formlink']) : ''; ?>'> 
    <?php if( isset($monitoring_row['formlink']) && $monitoring_row['formlink'] ) { 
        echo( "<A href='" . htmlspecialchars($monitoring_row['formlink']) . "'>View Form</a>" ); 
    } ?>
<br><br></td></tr>
</table>
 </td>
</tr>

<tr>
<td valign="top" bgcolor="#FFFFFF" colspan="2">
                <br>
                <?php 
                $showButtons = false;
                if( (isset($readonly) && !$readonly) || (isset($thisusersrow["tcfaculty"]) && $thisusersrow["tcfaculty"]) ) 
                {
                    $showButtons = true;
                }
                ?>
                <?php if( $showButtons ) { ?>
                <div align="center">
                    <input type="submit" value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if( isset($monitoringid) && $monitoringid ){ ?>
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
<?php } ?>
                </div>
<?php } else if( isset($monitoringid) && $monitoringid && isset($monitoring_row["monitoringdate"]) && !$monitoring_row["monitoringdate"] ) { ?>
                <div align="center">
                     <input onclick="return confirmDelete()" type="Submit" name="delete" value="Delete">
                </div>
<?php } ?>
</td></tr></table>
        <?php include "ssi/footer.php"; ?>
</span>
</td>
<td valign="top" width="15"><img src="images/dotclear.gif" width="10"></td>
</tr>
</table>
<br><br>
</div>
</form>
<script language='javascript'>
function datePickerClosed( ele )
{
    // alert( "hello" );
    if( ele && ele.value )
    {
        var d = new Date(ele.value);
        
        var year = d.getFullYear();
        var month = d.getMonth();
        var day = d.getDate();
        var c = new Date(year + 1, month, day+1);
        
        var nextField = document.getElementById("nextone");
        if( nextField )
        {
            nextField.value = c.getFullYear() + "-" + ("0"+(c.getMonth()+1)).slice(-2) + "-" + ("0"+(c.getDate())).slice(-2);
        }
    }
}
</script>
</body>
</html>