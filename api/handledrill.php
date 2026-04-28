<?php

$schoolname_raw = $_POST["schoolname"];
$schoolname_parts = explode("\n", $schoolname_raw);
$schoolname = array_shift($schoolname_parts);

$uploader = mysql_escape_string($_POST["uploader"]);
$esr_name = mysql_escape_string($_POST["esr_name"]);
$name     = mysql_escape_string($_POST["name"]);
$version  = mysql_escape_string($_POST["version"]);
$s_name   = mysql_escape_string($schoolname);

// 3. Insert into appuploads
$query = "INSERT INTO appuploads (dateuploaded, type, uploader, dateinupload, esi_repname, name, version, schoolname) 
          VALUES (NOW(), 'drill', '$uploader', '$signdate', '$esr_name', '$name', '$version', '$s_name')";
          
$newid = db_query_insert_id($query);
$drilldate = $signdate;

// $thedrillid = "";
// $schoolid = "";

// 4. Process the $data object (decoded in the previous step)

    foreach ($data as $key => $value) {
        if ($key === "drillid") $thedrillid = $value;
        if ($key === "id") $schoolid = $value;
        
        if ($key === "drillinfo") {
            foreach ($value as $particularname => $particulardata) {
                if ($particularname === "stepsdata") {
                    foreach ($particulardata as $step) {
                        $checked_val = (isset($step->ischecked) && $step->ischecked == "yes") ? 1 : 0;
                        
                        $stepnum  = mysql_escape_string($step->stepnumber);
                        $pts      = mysql_escape_string($step->points);
                        $stime    = mysql_escape_string($step->time);
                        $comments = mysql_escape_string($step->comments);

                        db_query("INSERT INTO drilluploaddata (uploadid, stepnumber, points, time, ischecked, comments) 
                                  VALUES ('$newid', '$stepnum', '$pts', '$stime', '$checked_val', '$comments')");
                    }
                }
                
                // If $particulardata is an array/object, we must encode it to string before DB insert
                $val_to_save = is_scalar($particulardata) ? $particulardata : json_encode($particulardata);
                $clean_val = mysql_escape_string($val_to_save);
                
                db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$particularname', '$clean_val')");
            }
        } else {
            $val_to_save = is_scalar($value) ? $value : json_encode($value);
            $clean_val = mysql_escape_string($val_to_save);
            db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$key', '$clean_val')");
        }
    }


// 5. Update schoolid

db_query("UPDATE appuploads SET schoolid = '$schoolid' WHERE id = '$newid'");

// 6. Process remaining $_POST data
foreach ($_POST as $key => $value) {
    if ($key === "apikey") continue;
    
    $clean_key = mysql_escape_string($key);
    $clean_val = mysql_escape_string($value);
    
    db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$clean_key', '$clean_val')");
}

// 7. Signature Handling
$files_to_process = [
    "media_file" => "media_file",
    "media_file_esr" => "media_file_esr"
];

foreach ($files_to_process as $file_key => $db_name) {
    if (!empty($_FILES[$file_key]["tmp_name"])) {
        $prefix = ($file_key === "media_file_esr") ? "_esr_" : "_";
        $sanitized_filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES[$file_key]["name"]);
        $signname = $newid . $prefix . $sanitized_filename;
        
        if (move_uploaded_file($_FILES[$file_key]["tmp_name"], "../signatures/" . $signname)) {
            $clean_signname = mysql_escape_string($signname);
            db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$db_name', '$clean_signname')");
        }
    }
}
?>