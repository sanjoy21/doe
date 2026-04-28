<?php
include "mysql.php";

if(!isset($session_userid) || $session_userid != "sarahg@emergencyskills.com" || !isset($id) || !$id)
{
    header("Location: index.php");
    exit;
}

if(isset($update) && $update)
{
    if(isset($_POST['position']) && is_array($_POST['position']))
    {
        foreach($_POST['position'] as $p)
        {
            $escaped_id = intval($id);
            $escaped_p = intval($p);
            $sql = "delete from responder_training_dates where responderid in (select responderid from responder_to_class where classid = $escaped_id and position = $escaped_p) and classid = $escaped_id";
            db_query($sql);
            // echo( $sql . "<br>" );
        }
    }
}

$crow = getClassRow($id);
$attendees = get_attendees($crow["id"]);
// print_r( $attendees );
?>
<?php include "ssi/top.php"; ?>
<a href='class_detail.php?id=<?php echo intval($id); ?>'>Back to class</a>
<form method='post'>
    <b>Remove Certifications From: </b>
    <table>
<?php
if(isset($attendees) && is_array($attendees))
{
    foreach($attendees as $i => $arow)
    {
        $attendee = array();
        if(isset($arow["responderid"]) && $arow["responderid"])
        {
            $attendee = get_attendee($arow["responderid"]);
        }
        
        if(isset($arow["completed"]) && $arow["completed"]) 
        {
            echo "<tr><td><input type='checkbox' name='position[]' value='" . intval($i) . "'> " . intval($i) . ".</td><td>" . 
                 (isset($attendee["firstname"]) ? htmlspecialchars($attendee["firstname"]) : "") . " " . 
                 (isset($attendee["lastname"]) ? htmlspecialchars($attendee["lastname"]) : "") . "</td></tr>";
        }
    }
}
?>
</table>
<input type='submit' name='update' value='Delete Checked Certifications'>
</form>
<br><br><br><br>

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