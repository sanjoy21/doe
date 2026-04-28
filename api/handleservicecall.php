<?php

$type = $method == "uploadNewInstall" ? "ni" : "sc";

$schoolname_raw = $_POST["schoolname"];
$schoolname_parts = explode("\n", (string)$schoolname_raw);
$schoolname = array_shift($schoolname_parts);

$uploader_clean = mysql_escape_string($_POST["uploader"]);
$esr_name_clean = mysql_escape_string($_POST["esr_name"]);
$name_clean     = mysql_escape_string($_POST["name"]);
$version_clean  = mysql_escape_string($_POST["version"]);
$school_clean   = mysql_escape_string($schoolname);

$newid = db_query_insert_id("INSERT INTO appuploads (dateuploaded, type, uploader, dateinupload, esi_repname, name, version, schoolname) 
                             VALUES (NOW(), '$type', '$uploader_clean', '$signdate', '$esr_name_clean', '$name_clean', '$version_clean', '$school_clean')");

$scdate = $signdate;
// $theservicecallid = "";
// $schoolid = "";

// 3. Process the $data loop

    foreach ($data as $key => $value) {
        if ($key == "id") $schoolid = $value;
        if ($key == "serviceid") $theservicecallid = $value;
        
        if ($key == "servicecallid") {
            
            $already = db_query_first_cell("SELECT uploadid FROM appuploaddata WHERE name = 'servicecallid' AND value = '$theservicecallid'");
            
            if ($already) {
                
                $comid = db_query_first_cell("SELECT value FROM appuploaddata WHERE uploadid = '$already' AND name = 'id'");
                
                $newsc = db_query_insert_id("INSERT INTO servicecall (companyid) VALUES ('$comid')");
                $value = $newsc; 
            }
            $theservicecallid = $value;
        }

        if (($key == "servicedata" || $key == "installdata") && is_iterable($value)) {
            foreach ($value as $num => $singledata) {
                $serial = mysql_escape_string($singledata->serial_number);
                $scid = db_query_insert_id("INSERT INTO scuploaddata (uploadid, serial) VALUES ('$newid', '$serial')");
                
                foreach ($singledata as $skey => $sval) {
                    if (in_array($skey, ["adultpadA", "adultpadB", "pediatric"]) && (is_array($sval) || is_object($sval))) {
                        foreach ($sval as $another => $aval) {
                            $clean_aval = mysql_escape_string(stripslashes((string)$aval));
                            db_query("INSERT INTO scuploaddetail (dataid, name, value) VALUES ('$scid', '{$skey}_{$another}', '$clean_aval')");
                        }
                    } else {
                        // Ensure $sval is a string before escaping
                        $val_str = is_scalar($sval) ? (string)$sval : json_encode($sval);
                        $clean_sval = mysql_escape_string(stripslashes($val_str));
                        db_query("INSERT INTO scuploaddetail (dataid, name, value) VALUES ('$scid', '{$skey}', '$clean_sval')");
                    }
                }
            }
        } else {
            $val_str = is_scalar($value) ? (string)$value : json_encode($value);
            $clean_val = mysql_escape_string($val_str);
            db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$key', '$clean_val')");
        }
    }


// 4. Notifications and schoolid update
if (empty($_FILES["media_file"]["tmp_name"])) {
    $post_dump = print_r($_POST, true);
    $file_dump = print_r($_FILES, true);
    mail("cox@vireo.org", "no files for uploaded service call ($newid)?", "id: $newid, \n$file_dump\n$post_dump", "From: info@emergencyskills.com");
}


db_query("UPDATE appuploads SET schoolid = '$schoolid' WHERE id = '$newid'");

// 5. Signature File Handling
$signatures = [
    "media_file" => "", // Standard signature
    "media_file_esr" => "_esr_" // ESR signature
];

foreach ($signatures as $file_key => $prefix) {
    if (!empty($_FILES[$file_key]["tmp_name"])) {
        // Sanitize filename to prevent directory traversal
        $filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES[$file_key]["name"]);
        $signname = $newid . $prefix . $filename;
        
        if (move_uploaded_file($_FILES[$file_key]["tmp_name"], "../signatures/" . $signname)) {
            $clean_signname = mysql_escape_string($signname);
            db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$file_key', '$clean_signname')");
        }
    }
}
?>