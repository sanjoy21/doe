<?php

$schoolname_raw = $_POST["schoolname"];
$schoolname_parts = explode("\n", (string)$schoolname_raw);
$schoolname = array_shift($schoolname_parts);

// 2. Escape variables (Replacing deprecated mysql_escape_string)
$uploader_clean = mysql_escape_string($_POST["uploader"]);
$esr_name_clean = mysql_escape_string($_POST["esr_name"]);
$name_clean     = mysql_escape_string($_POST["name"]);
$version_clean  = mysql_escape_string($_POST["version"]);
$school_clean   = mysql_escape_string($schoolname);
$signdate_clean = mysql_escape_string($signdate);

$newid = db_query_insert_id("INSERT INTO appuploads (dateuploaded, type, uploader, dateinupload, esi_repname, name, version, schoolname) 
                             VALUES (NOW(), 'ni', '$uploader_clean', '$signdate_clean', '$esr_name_clean', '$name_clean', '$version_clean', '$school_clean')");

$scdate = $signdate;
// $schoolid = "";

// 3. Process $data

    foreach ($data as $key => $value) {
        if ($key == "id") $schoolid = $value;
        
        if ($key == "servicecallid") {
            // Using a temporary variable to prevent "undefined" errors
            
            $already = db_query_first_cell("SELECT uploadid FROM appuploaddata WHERE name = 'servicecallid' AND value = '$theservicecallid'");
            
            if ($already) {
                
                $comid = db_query_first_cell("SELECT value FROM appuploaddata WHERE uploadid = '$already' AND name = 'id'");
                
                $newsc = db_query_insert_id("INSERT INTO servicecall (companyid) VALUES ('$comid')");
                $value = $newsc; 
            }
            $theservicecallid = $value;
        }

        if ($key == "installdata") {
            // Log/Debug output
            // print_r($value); 

            // Ensure $value is an object before accessing serialnumber
            $serial = (is_object($value) && isset($value->serialnumber)) ? $value->serialnumber : '';
            $serial_clean = mysql_escape_string($serial);
            
            $scid = db_query_insert_id("INSERT INTO scuploaddata (uploadid, serial) VALUES ('$newid', '$serial_clean')");
            
           
                foreach ($value as $skey => $sval) {
                    if (in_array($skey, ["adultpadA", "adultpadB", "pediatric"]) && (is_array($sval) || is_object($sval))) {
                        foreach ($sval as $another => $aval) {
                            $clean_aval = mysql_escape_string($aval);
                            db_query("INSERT INTO scuploaddetail (dataid, name, value) VALUES ('$scid', '{$skey}_{$another}', '$clean_aval')");
                        }
                    } else {
                        $clean_sval = mysql_escape_string($sval);
                        db_query("INSERT INTO scuploaddetail (dataid, name, value) VALUES ('$scid', '{$skey}', '$clean_sval')");
                    }
                }
            
        } else {
            // Handle regular appuploaddata
            $val_str = is_scalar($value) ? (string)$value : json_encode($value);
            $clean_val = mysql_escape_string($val_str);
            db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$key', '$clean_val')");
        }
    }


// 4. Update schoolid

db_query("UPDATE appuploads SET schoolid = '$schoolid' WHERE id = '$newid'");

// 5. Signature Handling
$file_fields = [
    "media_file" => "",
    "media_file_esr" => "_esr_"
];

foreach ($file_fields as $field => $prefix) {
    if (!empty($_FILES[$field]["tmp_name"])) {
        // Sanitize the filename for PHP 8.2 security
        $filename = preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES[$field]["name"]);
        $signname = $newid . $prefix . $filename;
        
        if (move_uploaded_file($_FILES[$field]["tmp_name"], "../signatures/" . $signname)) {
            $clean_signname = mysql_escape_string($signname);
            db_query("INSERT INTO appuploaddata (uploadid, name, value) VALUES ('$newid', '$field', '$clean_signname')");
        }
    }
}
?>