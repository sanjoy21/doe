<?php
require_once('mysql.php');
require_once('services.php');

// Initialize variables if they are not set (assuming they come from $_POST or $_GET)
$responderid = $responderid ?? 0;
$newclass = $newclass ?? 0;
$classid = $classid ?? 0;
$specialadmin = $specialadmin ?? false;
$companyid = $companyid ?? 0;
$confirm = "";
$is_added = false;

$rrow = getResponderRow( $responderid );

if ($newclass) {
    // Use quoted array keys
    $crow = getClassRow( $newclass );
    $attendees = get_attendees( $newclass );
    $valid = true;

    // Use quoted array keys
    if (!$crow['id']) {
        $confirm = "<div id='error'>Sorry, this is not a valid class #.</div>";
        $valid = false;
    }
    // Use quoted array keys
    else if (count( $attendees ) >= $crow['maxattendees']) {
        $confirm = "<div id='error'>Sorry, this class is full.</div>";
        $valid = false;
    }
    
    if( $valid )
    {
        $toaddnum = -1;
        // Use quoted array keys
        for( $i = 1; $i <= $crow['maxattendees']; $i++ )
        {
            // Use quoted array keys
            $exists = db_query_first_cell( "select responderid from responder_to_class where classid = $newclass and position = $i" );
            $toaddnum = $i;
            if( !$exists )
            {
                break;
            } 
        }
        
        // 1. Add attendee to the new class
        addAttendee( time(), $newclass, $responderid, $toaddnum, "" );
        $is_added = true;

        // 2. Remove attendee from the old class and clean up positions
        $pos = db_query_first_cell( "select position from responder_to_class where classid = $classid and responderid = $responderid" );
        db_query( "delete from responder_to_class where classid = $classid and responderid = $responderid" );
        
        if( $pos )
        {
            // Shift subsequent positions up by 1
            db_query( "update responder_to_class set position = position - 1 where classid = $classid and position > '$pos'" );
        }
    }
}

// Get details for the current (old) class
$classrow = getClassRow( $classid );

// Fetch other classes that are accepted, not deleted, not locked, and in the future
// Use quoted array keys
$otherclasses = db_query_rows( "select * from class where accepted = 1 and deleted = 0 and islocked = 0 and code = '{$classrow['code']}' and startdate > now() order by startdate" );

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>ESI: Move Attendee</title>

<META NAME="Keywords" CONTENT="">

<META NAME="Description" CONTENT="">     
 
<?php 
if( $specialadmin )
{
    // Use quoted array keys
    ${"selected_".$companyid} = "SELECTED";
    include "getschooldropdown.php"; 
}
?>
    

<link rel="stylesheet" href="css/style.css">

</head>

<body bgcolor="#ffffff" marginwidth="20" marginheight="20">
<form method="post">
<input type="hidden" name="action" value="add">
<input type="hidden" name="classid" value="<?php echo $classid; ?>">
<input type="hidden" name="responderid" value="<?php echo $responderid; ?>">
<span class="copy">
<strong><span class="title">Move Attendee - <?php echo $rrow['firstname'] . " " . $rrow['lastname']; ?></span></strong><p>

<font color='red'><?php echo $confirm; ?></font>
What is the new class #? <input type='text' size='5' name='newclass' value=''> <input type='submit' name='go' value='Go'>
      <input type="button" name='close' value='Close' onClick='javascript:window.close()'>

<table border=1 cellspacing=0>
<?php foreach( $otherclasses as $o ) {
    // Use quoted array keys
    $attendees = get_attendees( $o["id"] );
    // Use quoted array keys
    if( count( $attendees ) >= $o["maxattendees"] )
    {
        continue;
    }
    // Use quoted array keys
    $numleft = $o["maxattendees"] - count( $attendees ) ;
    // Use quoted array keys
    $crow = getCompanyRow( $o['companyid'] );

    ?>
<tr>
    <!-- Use quoted array keys -->
    <td><?php echo $o['id']; ?></tD>
    <td>
    <!-- Use quoted array keys -->
    <?php echo getCompanyName( $o['companyid'] ); ?><br>
    <?php echo $crow['schoolcode']; ?><br>
    <?php echo getTrainingAddress( $o ); ?><br>
    <?php echo getFormattedDateWTime( $o['startdate'] ); ?>
    </td>
    <td>
    <?php echo $numleft; ?> spots left
    </td>
</tr>
<?php } ?>
</table>
<?php if ($is_added) { ?>
<script type="text/javascript">
                     window.opener.location.reload();    
                     setTimeout('window.close()', 1000);
</script>
<?php } ?>

</form>
</body>
</html>