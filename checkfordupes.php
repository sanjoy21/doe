<?php 
require "mysql.php";

// Safely retrieve external variables
$save = $_POST['save'] ?? null;
$okay = $_POST['okay'] ?? []; // Array of PMS IDs marked as 'OK'
$db_link = $GLOBALS['link'] ?? $link;

// --- 1. Save Logic ---
if( $save )
{
    // Clear the existing checked IDs table
    db_query( "DELETE FROM pmsidchecks" ); 
    
    // Insert the newly marked 'OK' PMS IDs
    if (!empty($okay) && is_array($okay)) {
        foreach( $okay as $pm )
        {
            $safe_pm = mysqli_real_escape_string($db_link, $pm);
            db_query( "INSERT INTO pmsidchecks ( pmsid ) VALUE ( '{$safe_pm}' )" );
        }
    }
}

// --- 2. Find PMS IDs with Duplicates ---
// The original query finds PMS IDs that appear more than once (duplicates) and filters out some known test/junk IDs.
$pmsids_rows = db_query_rows( "SELECT 
                                    CAST(castedpmsid AS CHAR) AS castedpmsid, COUNT(*) AS num_duplicates
                                FROM 
                                    responders_esi 
                                WHERE 
                                    deleted = 0 
                                    AND CAST(castedpmsid AS SIGNED) > 9999 
                                    AND castedpmsid NOT IN ( '123456', '12345', '1234567' ) 
                                GROUP BY 
                                    castedpmsid 
                                HAVING 
                                    COUNT(*) > 1", 
                                "castedpmsid", 
                                "castedpmsid" );

// Get the unique list of duplicate PMS IDs
$pmsids = array_keys($pmsids_rows);

// --- 3. Fetch all responder records associated with the duplicate PMS IDs ---
if (empty($pmsids)) {
    $res = [];
    $pms_in_clause = "''";
} else {
    // Escape all duplicate PMS IDs for use in the IN clause
    $safe_pms_ids = array_map(function($p) use ($db_link) {
        return "'" . mysqli_real_escape_string($db_link, $p) . "'";
    }, $pmsids);
    
    $pms_in_clause = implode( ", " , $safe_pms_ids );
    
    // The original query filters for non-corporate, non-deleted, specific regions/school codes, and orders by responderid DESC
    $sql = "SELECT 
                r.* FROM 
                responders_esi r 
            JOIN 
                company_esi c ON c.id = r.clientid 
            WHERE 
                c.iscorp = 0 
                AND c.deleted = 0 
                AND r.deleted = 0 
                AND r.pmsid > '' 
                AND r.castedpmsid IN ( {$pms_in_clause} ) 
                AND r.region NOT LIKE '%ssa%' 
                AND c.schoolcode NOT LIKE '84%' 
            ORDER BY 
                r.responderid DESC";
    $res = db_query_rows($sql );
}

// --- 4. Output HTML Form and Table ---
?>
<form method='post'>
<input type='submit' name='save' value='Save'>
<table border=1>
<tr>
    <th>OK?</th>
    <th>count</th>
    <th>name</th>
    <th>last validated</th>
    <th>pmsid</th>
    <th>school</th>
    <th>matches</th>
</tr>
<?php
$already = array();
$count = 0;
// Assuming getCompanyName() exists and is safe
foreach( $res as $r )
{
    $pmsid = $r['pmsid'] ?? '';
    $responderid = $r['responderid'] ?? null;
    $castedpmsid = $r['castedpmsid'] ?? '';
    
    if( isset($already[$pmsid]) ) continue;
    $already[$pmsid] = 1;

    // --- Find all other responders with the same casted PMS ID (who were created earlier) ---
    $sql = "SELECT * FROM responders_esi 
            WHERE castedpmsid = '" . mysqli_real_escape_string($db_link, $castedpmsid) . "' 
            AND responderid < " . (int)$responderid . " 
            AND deleted = 0";
    $other = db_query_rows( $sql );
    
    // If this responder doesn't have an 'earlier' match, skip it (as the main query fetched based on the dupe list)
    if( !count( $other ) ) continue;

    $poss = array();
    foreach( $other as $o )
    {
        $cls = "";
        // Highlight if names don't match (suggesting a serious error)
        if( strtolower( trim( $o['firstname'] ?? '' ) ) != strtolower( trim( $r['firstname'] ?? '' ) ) 
            || strtolower( trim( $o['lastname'] ?? '' ) ) != strtolower( trim( $r['lastname'] ?? '' ) ) )
        {
            $cls = "style='background-color:#ffffdd;'";
        }
        
        $o_responderid_safe = htmlspecialchars($o['responderid'] ?? '');
        $o_firstname_safe = htmlspecialchars($o['firstname'] ?? '');
        $o_lastname_safe = htmlspecialchars($o['lastname'] ?? '');
        $o_company_safe = htmlspecialchars(getCompanyName( $o['clientid'] ?? null ));
        $o_pmsid_safe = htmlspecialchars($o['pmsid'] ?? '');
        $o_lastvalidated_safe = htmlspecialchars($o['lastpmsvalidated'] ?? '');

        $poss[] = "<tr {$cls}>
                      <td><a href='viewresponder.php?responderid={$o_responderid_safe}' target='_blank'>{$o_firstname_safe} {$o_lastname_safe}</a></td>
                      <td>{$o_company_safe}</td>
                      <td>{$o_pmsid_safe}</td>
                      <td>{$o_lastvalidated_safe}</td>
                   </tr>";
    }

    $poss_html = implode( "", $poss ); // Changed <br> to "" since rows should be inside <table>
    
    $r_responderid_safe = htmlspecialchars($responderid);
    $r_firstname_safe = htmlspecialchars($r['firstname'] ?? '');
    $r_lastname_safe = htmlspecialchars($r['lastname'] ?? '');
    $r_lastvalidated_safe = htmlspecialchars($r['lastpmsvalidated'] ?? '');
    $r_pmsid_safe = htmlspecialchars($pmsid);
    $r_company_safe = htmlspecialchars(getCompanyName( $r['clientid'] ?? null ));

    // Check if this PMS ID was previously marked as OK
    $chk_pmsid = mysqli_real_escape_string($db_link, $pmsid);
    $chk = db_query_first_cell( "SELECT pmsid FROM pmsidchecks WHERE pmsid = '{$chk_pmsid}'" ) ? "CHECKED" : "";
    
    echo( "<tr>" );
    echo( "<td><input type='checkbox' name='okay[]' {$chk} value='{$r_pmsid_safe}'></td>" );
    echo( "<td>" . (++$count) . "</td>" );
    echo( "<td><a href='viewresponder.php?responderid={$r_responderid_safe}'>{$r_firstname_safe} {$r_lastname_safe}</a></td>" );
    echo( "<td>{$r_lastvalidated_safe}</td>" );
    echo( "<td>{$r_pmsid_safe}</td>" );
    echo( "<td>{$r_company_safe}</td>" );
    echo( "<td><table>{$poss_html}</table></td>" );
    echo("</tr>" );
    
}
?>
</table>
<input type='submit' name='save' value='Save'>

</form>