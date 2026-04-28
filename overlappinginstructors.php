<?php

$nologinrequired = true;
include "mysql.php";
require_once "services.php";

// Calculate next week's date dynamically (original logic)
$nextweek = date( "Y-m-d", mktime( 0,0,0,date( "m" ), date( "d" )+7, date("Y" ) ) );
$dt = date( "Y-m-d" );

// --- Original hardcoded overrides commented out, using dynamic dates instead ---
// $nextweek = "2019-11-25";
// $dt = "2019-11-25";
// -----------------------------------------------------------------------------

$already = array();

while( $dt <= $nextweek )
{
    // Fetch classes for the current day $dt
    $sql = "select trainer_to_class.trainerid, startdate, enddate, class.id, code 
            from class, trainer_to_class 
            where class.id = classid 
            and startdate like '{$dt}%' 
            and canceldate is null 
            order by startdate";
    
    $classes = db_query_rows( $sql );

    $trainersthisday = array();
    foreach( $classes as $crow )
    {
        // Calculate the effective end date/time
        // Use quoted array keys
        $end_time_part = $crow['enddate'];
        $start_date_part = $crow['startdate'];
        $trainer_id = $crow['trainerid'];
        $class_id = $crow['id'];

        if( $end_time_part == "12:00 AM" ) 
        {
            // Classes without proper end time defaulted to + 6 hours
            $enddt = date( "Y-m-d H:i:s", strtotime( $start_date_part . " + 6 hours" ) );
        }
        else
        {
            $enddt = date( "Y-m-d", strtotime( $start_date_part ) ) . " " . $end_time_part;
        }
        
        $starttm = strtotime( $start_date_part );
        $endtm = strtotime( $enddt );

        // Check for overlaps with existing classes for this trainer on this day
        // Use safer null coalescing operator to check if $trainersthisday[$trainer_id] exists
        foreach( $trainersthisday[$trainer_id] ?? [] as $existingclasses )
        {
            // Overlap condition 1: Existing class starts before current class ends AND existing class ends after current class starts.
            // Simplified condition: Check if the start or end time of the *current* class falls within the *existing* class's time range.
            
            // Check if current class starts within existing class period
            $starts_overlap = ($existingclasses["starttime"] <= $starttm && $existingclasses["endtime"] > $starttm);

            // Check if current class ends within existing class period
            $ends_overlap = ($existingclasses["starttime"] <= $endtm && $existingclasses["endtime"] > $endtm);

            // Check if existing class is fully contained within current class (important for complete coverage)
            $existing_contained = ($starttm <= $existingclasses["starttime"] && $endtm >= $existingclasses["endtime"]);

            if( $starts_overlap || $ends_overlap || $existing_contained )
            {
                // Format display times
                $displst = date( "Y-m-d h:i a", strtotime( $start_date_part ) );
                $displet = date( "h:i a", strtotime( $enddt) );
                $displst2 = date( "Y-m-d h:i a", strtotime( $existingclasses["startdate"] ) );
                $displet2 = date( "h:i a", strtotime( $existingclasses["enddate"] ) );

                // Use quoted array keys for getFullname
                $already[] = getFullname( $trainer_id ) . " has overlapping classes: " . 
                             "<a target=_blank href='https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/class_detail.php?id=$class_id'>$class_id</a> $displst - $displet overlaps with " . 
                             "<a target=_blank href='https://".SUB_DOE."." . URL_WITHOUT_SUBDOMAIN . "/class_detail.php?id=" . $existingclasses['classid'] . "'>" . $existingclasses['classid'] . "</a> $displst2 - $displet2";
            }
        }
        
        // Add the current class to the trainer's schedule for the day
        $trainersthisday[$trainer_id][] = array( 
            "classid" => $class_id, 
            "startdate" => $start_date_part, 
            "enddate" => $enddt, 
            "starttime" => $starttm, 
            "endtime" => $endtm 
        );
    }

    // Move to the next day
    $dt = date( "Y-m-d", strtotime( "{$dt} + 1 day" ) );
}

// Send email if overlaps are found
if( count( $already ) > 0 )
{
    $body = implode( "<br><br>", $already );
    // Commented out the first email address, keeping the active one
    // sendHTMLMail( "rachelc@gmail.com", "ESI: Overlapping instructors", $body,"info@emergencyskills.com" );
    sendHTMLMail( "barbara@emergencyskills.com", "ESI: Overlapping instructors", $body,"info@emergencyskills.com" );
}
?>