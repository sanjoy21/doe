<?php 
include "mysql.php";

$upload = $_POST['upload'] ?? null;

if( $upload )
{
    $file_tmp_name = $_FILES["filename"]["tmp_name"] ?? null;
    
    echo( "in here upload" );
    
    if ($file_tmp_name && is_uploaded_file($file_tmp_name)) {
        $handle = @fopen($file_tmp_name, "r");
        
        if ($handle === FALSE) {
            echo "Error: Could not open uploaded file.";
        } else {
            // Loop through the CSV file
            while (($data = fgetcsv($handle, 9999, ",")) !== FALSE) { 
                
                // Skip rows that don't have at least 3 columns (DBN, ?, Color)
                if (!isset($data[0]) || !isset($data[2])) {
                    continue;
                }
                
                // Sanitize and escape data
                $dbn = trim($data[0]);
                $color = trim($data[2]);
                $safe_color = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $color);
                
                if( $dbn == "DBN" )
                {
                    echo( "clearing" );
                    db_query( "DELETE FROM badschoolids" );
                    continue;
                }
                
                if( $color === '' ) continue;
                
                // Format the new school code (e.g., A001 -> A-0-001)
                $newcode = substr( $dbn, 0, 2 ) . "-". substr( $dbn, 2, 1 ) . "-" . substr( $dbn, 3, 3 );
                
                // --- 1. Find matching school in company_esi ---
                // Escape the formatted code for security
                $safe_newcode = mysqli_real_escape_string($GLOBALS['link'] ?? $link, $newcode);
                
                $res = db_query_first_cell( "SELECT id FROM company_esi WHERE schoolcode = '{$safe_newcode}' AND iscorp = 0" );
                
                if( $res )
                {
                    // Safety: Cast result to integer for use in SQL
                    $school_id = (int)$res;
                    
                    // --- 2. Insert into badschoolids table ---
                    $sql_insert = "INSERT INTO badschoolids ( schoolid, color, dateadded) 
                                   VALUES ( {$school_id}, '{$safe_color}', NOW() )";
                    db_query( $sql_insert );
                    
                    echo( "added {$school_id}, {$dbn}, {$color}<Br>" );
                }
                else
                {
                    echo( "didn't find a match for {$dbn}, {$newcode}, {$color}<br> " );
                }
            } // end while fgetcsv
            
            @fclose($handle);
        }
    } else {
        echo "Error: File upload failed or file was not received.";
    }
}
?>
<form method='post' enctype='multipart/form-data'>
<input type='file' name='filename'> 
<input type='submit' name='upload' value='Upload Closed Schools'>
<i> CSV with DBN first, then Color</i>
</form>