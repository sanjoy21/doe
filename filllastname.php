<?php 
$nologinrequired = true;
require_once('mysql.php');

// Safely access and process external variables
$lastname_input = $lastname ?? '';
$classid_safe = $classid ?? 0;
$spotid_safe = $spotid ?? '';

// Split the incoming $lastname string into last and first names
// Note: Assumes $lastname is in the format "Last Name, First Name"
$explode = explode( ", ", $lastname_input );
$lastname_search = $explode[0] ?? '';
$firstname_search = $explode[1] ?? '';

// SQL query to find a single attendee who doesn't have a training date recorded for this class
$sql = "select * from responder_to_class, responders_esi 
        where attended = 1 
          and lastname like '" . $lastname_search . "%' 
          and firstname like '" . $firstname_search . "%' 
          and classid = '" . (int)$classid_safe . "' 
          and responders_esi.responderid = responder_to_class.responderid 
          and responder_to_class.responderid not in ( select responderid from responder_training_dates where classid = '" . (int)$classid_safe . "' )";

$res = db_query_rows( $sql );

if( count( $res ) == 1 )
{
    $arr = array_pop( $res );
    
    // Safely access quoted array keys
    $lastname_found = $arr["lastname"] ?? '';
    $firstname_found = $arr["firstname"] ?? '';
    $id_found = $arr["responderid"] ?? '';
    
    $name = $lastname_found . ", " . $firstname_found;
    
    // Output JavaScript to update form fields
    echo( "document.getElementById( 'namedisplay" . $spotid_safe . "').innerHTML = \"" . htmlentities( $name ). "\";\n" );
    echo( "document.getElementById( 'lastname" . $spotid_safe . "').value = \"" . htmlentities( $name ). "\";\n" );
    echo( "document.getElementById( 'hiddencompletion" . $spotid_safe . "').value = \"" . htmlentities( $id_found ). "\";\n" );

}
else
{
    // No action if 0 or >1 results found
}

?>