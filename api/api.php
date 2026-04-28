<?php
if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg() {
        static $ERRORS = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );

        $error = json_last_error();
        return isset($ERRORS[$error]) ? $ERRORS[$error] : 'Unknown error';
    }
}

$nologinrequired = true;
include "../mysql.php";

// Initialize variables from POST/GET
$method = $_POST['method'] ?? $_GET['method'];
$uploader = $_POST['uploader'] ?? $_GET['uploader'];
$apikey = "daMEs26rufAqasw2pUYU";
$username = $_POST['username'] ?? $_GET['username'];
$password = $_POST['password'] ?? $_GET['password'];
$userid = $_POST['userid'] ?? $_GET['userid'];
$zipcode = $_POST['zipcode'] ?? $_GET['zipcode'];
$schoolid = $_POST['schoolid'] ?? $_GET['schoolid'];

function getPostData()
{
    global $_POST, $method, $uploader;
    
    // if (!isset($_POST["data"])) {
    //     return null;
    // }
    
    $data = json_decode(stripslashes($_POST["data"]));
    
    if (json_last_error()) {
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . ": error parsing data: " . json_last_error_msg() . "\n", FILE_APPEND);
        file_put_contents("apilog.txt", "error parsing data: " . stripslashes($_POST["data"]) . "\n", FILE_APPEND);
        
        $a = mb_convert_encoding(stripslashes($_POST["data"]), 'UTF-8', 'ISO-8859-1');
        $data = json_decode(stripslashes($a));
    }
    
    if (json_last_error()) {
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . " error parsing data (2): " . json_last_error_msg() . "\n", FILE_APPEND);
        $a = $_POST["data"];
        $a = preg_replace('/[^(\x20-\x7F)]*/', '', $a);
        $data = json_decode(stripslashes($a));
        
        if (json_last_error()) {
            $body = $_POST["data"];
            $body .= "\n Parsed: " . print_r($data, true);
            mail("rachelc@gmail.com", "could not parse data!", $body, "From: info@emergencyskills.com");
            file_put_contents("apilog.txt", date("Y-m-d H:i:s") . " never could parse data (2): " . json_last_error_msg() . "\n", FILE_APPEND);
            
            // Use mysqli_real_escape_string instead of deprecated mysql_escape_string
            $post_data = mysql_escape_string(print_r($_POST, true));
            db_query("INSERT INTO api_errors (method, uploader, dateuploaded, postdata, retval) VALUES ('$method', '$uploader', NOW(), '$post_data', '" . json_last_error_msg() . "')");
        }
    }
    
    return $data;
}

file_put_contents("/tmp/rcaed", date("Y-m-d H:i:s") . " new post: " . print_r($_POST, true), FILE_APPEND);
file_put_contents("/tmp/rcaed", date("Y-m-d H:i:s") . " new post files: " . print_r($_FILES, true), FILE_APPEND);

$retval = array();
$valid_functions = array("doLogin", "doLogoff", "getSchools", "downloadData", "upload", "uploadServiceCall", "uploadDrill", "uploadNewInstall", "uploadRecertNotes");


$post_data = mysql_escape_string(print_r($_POST, true));
$insid = db_query_insert_id("INSERT INTO api_calls(dateuploaded, uploader, method, postdata) VALUES (NOW(), '$uploader', '$method', '$post_data')");

if (!in_array($method, $valid_functions)) {
    $retval["status"] = "error";
    $retval["error_message"] = "invalid method";
    echo json_encode($retval);
    exit;
}

if ($apikey != 'daMEs26rufAqasw2pUYU') {
    $retval["status"] = "error";
    $retval["error_message"] = "invalid api key";
    echo json_encode($retval);
    exit;
}

switch($method) { 
    case "doLogin": 
        // $username = addslashes($username);
        // $password = addslashes($password);
        $sql = "SELECT * FROM user WHERE userid = '$username' AND password = '$password' AND usertype='trainer' AND inactive = 0";
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . ": trying to log in: $sql!\n", FILE_APPEND);
        
        $val = db_query_first($sql);
        if (isset($val['id'])) {
            $attrs = array();
            $attrs["email"] = $val['userid'];
            $attrs["name"] = $val['first_name'] . " " . $val['last_name'];
            $attrs["firstname"] = $val['first_name'];
            $attrs["hascorponapp"] = $val['hascorponapp'];

            $ziparr = db_query_array("SELECT zip FROM user_to_zip WHERE userid = " . (int)$val['id'] . " ORDER BY zip DESC", "zip", "zip");
            $doarr = array();
            // if (is_array($ziparr)) {
                foreach($ziparr as $zip) {
                    $doarr[] = array("zip" => $zip);
                }
            // }
            
            $attrs["visiblezips"] = $doarr;
            $retval["status"] = "success";
            $retval["attrs"] = $attrs;
            $retval["expredbefore"] = strtotime(getsetting("expredbefore"));
            $retval["firstdrillday"] = strtotime(getsetting("firstdrillday"));
            $retval["lastdrillday"] = strtotime(getsetting("lastdrillday"));
            $retval["userid"] = $val['id']; 
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "invalid username/password combination";
            $retval["sql"] = $sql;
        }
        break;
        
    case "getSchools": 
        if ($userid == 6848) {
            file_put_contents("/tmp/logger", "");
        }

        $thisusersrow = getUserRow($userid);
        $isc = $thisusersrow['hascorponapp'] ? " 1 " : " iscorp = '0'";
        $isc .= " AND companyname NOT LIKE 'Responder Hold%'";
        
        if (isset($thisusersrow['userid'])) {
            $schools = array();
            
            if ($zipcode) {
                $schoolsarr = db_query_rows("SELECT *, (SELECT COUNT(*) FROM recertnotes WHERE completed = 0 AND companyid = company_esi.id) AS rncount FROM company_esi WHERE $isc AND deleted = 0 AND zip = '$zipcode' AND (showsondrillreports = 1 OR iscorp = 1) ORDER BY rncount DESC, zip, campusid", "id");
            } else {
                $myzips = getZips($thisusersrow);
                $extschools = "";
                if (isset($thisusersrow["extraschools"]) && $thisusersrow["extraschools"]) {
                    $extschools = " OR id IN ({$thisusersrow['extraschools']})";
                }
                
                $sql = "SELECT *, (SELECT COUNT(*) FROM recertnotes WHERE completed = 0 AND companyid = company_esi.id) AS rncount FROM company_esi WHERE $isc AND deleted = 0 AND (zip IN ($myzips) $extschools) AND (showsondrillreports = 1 OR iscorp = 1) ORDER BY rncount DESC, zip, campusid";
                $retval["sql"] = $sql;
                $schoolsarr = db_query_rows($sql, "id");
            }
            
            $dt = getsetting("drillsdontcountbefore");
            
            foreach($schoolsarr as $s) {
                if ($s['iscorp']) {
                    $existingaeds = getAedRows($s['id'], false, $s['campusid'], true);
                    if (!count($existingaeds)) {
                        continue;
                    }
                }
                
                $key = $s['zip'] . ($s['campusid'] ? " - " . getCampusName($s['campusid']) : "");
                
                if ($s['iscorp']) {
                    $needsdrill = 0;
                    $faileddrill = 0;
                } else {
                    $needsdrillquery = "SELECT d.drillid FROM drill d, drill_to_companyid dtc WHERE d.drillid = dtc.drillid AND (completed = 1 OR appid > 0) AND drilldate >= '$dt' AND dtc.companyid = " . (int)$s['id'];
                    $needsdrill = db_query_first_cell($needsdrillquery) ? 0 : 1;
                    
                    $faileddrillquery = "SELECT notrained FROM drill d, drill_to_companyid dtc WHERE d.drillid = dtc.drillid AND drilldate >= '$dt' AND dtc.companyid = " . (int)$s['id'] . " ORDER BY drilldate DESC LIMIT 1";
                    $faileddrill = db_query_first_cell($faileddrillquery) ? 1 : 0;
                }

                $rnstringarr = db_query_array("SELECT recertificationnotes FROM recertnotes WHERE completed = 0 AND companyid = " . (int)$s['id'], "recertificationnotes", "recertificationnotes");
                $rnstring = is_array($rnstringarr) ? implode("; ", $rnstringarr) : "";

                $attrs = array();
                $attrs["key"] = "";
                $attrs["faileddrill"] = $faileddrill;
                $attrs["needsdrill"] = $needsdrill;
                $attrs["rnnum"] = $s['rncount'];
                $attrs["rnstring"] = $rnstring;
                $attrs["iscorp"] = $s['iscorp'];
                $attrs["name"] = getCompanyNameWithColorString($s, false);
                $attrs["id"] = $s['id'];
                $attrs["zipcode"] = $s['zip'];
                $attrs["address"] = $s['address'];
                $attrs["city"] = $s['city'];
                $attrs["code"] = $s['schoolcode'];
                $attrs["campusid"] = $s['campusid'];
                $attrs["numresponders"] = getNumResponders($s['id'], true);
                
                $sixmonths = date("Y-m-d", strtotime(getsetting("expredbefore")));
                $or = "";
                if (!$s['iscorp'] && $s['campusid']) {
                    $or = " OR clientid IN (SELECT id FROM company_esi WHERE iscorp = 0 AND campusid = " . (int)$s['campusid'] . ")";
                }
                
                $num = db_query_first_cell("SELECT SUM(CASE WHEN padaexpiration < '$sixmonths' THEN 1 ELSE 0 END + CASE WHEN padbexpiration < '$sixmonths' THEN 1 ELSE 0 END) FROM aed_esi WHERE aedstolen = 0 AND aedinactive = 0 AND aedmissing = 0 AND deleted = 0 AND (clientid = " . (int)$s['id'] . " $or)");
                if (!$num) $num = 0;
                
                $attrs["numpads"] = $num;
                $attrs["campusname"] = $s['campusid'] ? getCampusName($s['campusid']) : "None";
                $schools[] = $attrs;
            }
            
            $retval["status"] = "success";
            $retval["schools"] = $schools;
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "invalid userid";
        }
        break;
        
    case "downloadData": 
        $thiscompanyrow = getCompanyRow($schoolid);
        
        if (isset($thiscompanyrow['id'])) {
            $retval['principalname'] = $thiscompanyrow['principalname'];
            $retval['principalphone'] = $thiscompanyrow['principalphone'];
            $retval['companyname'] = getCompanyNameWithColorString($thiscompanyrow, false);
            $retval['principalemail'] = $thiscompanyrow['principalemail'];
            $retval['schoolphone'] = $thiscompanyrow['schoolphone'];
            $retval['address'] = $thiscompanyrow['address'];
            $retval['city'] = $thiscompanyrow['city'];
            $retval['state'] = $thiscompanyrow['state'];
            $retval['zip'] = $thiscompanyrow['zip'];
            $retval['floor'] = $thiscompanyrow['floor'];
            $retval['schoolcode'] = $thiscompanyrow['schoolcode'];
            $retval['contactname'] = $thiscompanyrow['contactname'];
            $retval['iscorp'] = $thiscompanyrow['iscorp'];
            $retval['contactphone'] = $thiscompanyrow['contactphone'];
            $retval['contactemail'] = $thiscompanyrow['contactemail'];
            
            $otherschools = getSchoolsInCampus($thiscompanyrow["campusid"], $thiscompanyrow["id"]);
            $retval["otherschools_in_campus"] = $otherschools;

            include "downloaddata_api.php";
            $retval["status"] = "success";
            
            $retval["recertNotes"] = $recertnotes;
            if (!$thiscompanyrow['iscorp']) {
                $retval["drill"] = $drill;
                $retval["newinstall"] = $newinstall;
            }
            $retval["servicecall"] = $servicecall;
            $retval["existingaeds"] = $existingaeds;
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "invalid companyid";
        }
        break;
        
    case "upload":
        if (isset($_FILES["media_file"]["tmp_name"]) && $_FILES["media_file"]["tmp_name"]) {
            move_uploaded_file($_FILES["media_file"]["tmp_name"], "../signatures/" . $_FILES["media_file"]["name"]);
        }
        $retval["status"] = "success";
        break;

    case "uploadRecertNotes":
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . " about to upload recert notes!\n", FILE_APPEND);
        $data = getPostData();
        
        if ($data && isset($data->recertnotedata)) {
            $notes = $data->recertnotedata;
            $companyid = isset($data->id) ? (int)$data->id : 0;
            
            foreach($notes as $n) {
                $rnid = isset($n->rnid) ? (int)$n->rnid : 0;
                $tn = "Sent from App: " . (isset($n->trainernotes) ? $n->trainernotes : '');
                $tn .= "\nUploader: $uploader";
                $iscompleted = (isset($n->iscompleted) && $n->iscompleted == "yes") ? 1 : 0;
                
                file_put_contents("apilog.txt", "UPDATE recertnotes SET completed = $iscompleted, completednotes = '" . mysql_escape_string($tn) . "' WHERE id = $rnid AND companyid = $companyid\n", FILE_APPEND);
                db_query("UPDATE recertnotes SET completed = $iscompleted, completednotes = '" . mysql_escape_string($tn) . "' WHERE id = $rnid AND companyid = $companyid");
                
                if ($iscompleted) {
                    sendMail("sherry@emergencyskills.com", "Recert note $rnid completed via app", "Recert Note $rnid was completed via the app. \n Notes: $tn \n\n https://".SUB_DOE.".".URL_WITHOUT_SUBDOMAIN."/editrecertnotes.php?id=$companyid", "info@emergencyskills.com");
                }
            }
            
            file_put_contents("apilog.txt", $_SERVER["REMOTE_ADDR"] . ": SUCCESS PARSING data: " . print_r($data, true), FILE_APPEND);
            $retval["status"] = "success";
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "Invalid data format";
        }
        break;
        
    case "uploadDrill":
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . " about to upload drill!\n", FILE_APPEND);
        $data = getPostData();
        file_put_contents("apilog.txt", $_SERVER["REMOTE_ADDR"] . ": SUCCESS PARSING data: " . print_r($data, true), FILE_APPEND);
        
        if (isset($_POST["time"]) && isset($_POST["date"])) {
            $time = str_pad($_POST["time"], 6, "0", STR_PAD_LEFT);
            $signdate = substr($_POST["date"], 0, 4) . "-" . substr($_POST["date"], 4, 2) . "-" . substr($_POST["date"], 6, 2) . " " . substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);
            
            include "handledrill.php";
            
            if (isset($thedrillid)) {
                db_query("UPDATE drill SET appid = '$newid', isdone = 1, drilldate = '$drilldate' WHERE drillid = '$thedrillid'");
            }
            
            $frompending = isset($_POST["fromPending"]) && $_POST["fromPending"] == "true" ? 1 : 0;
            db_query("UPDATE appuploads SET frompending = '$frompending' WHERE id = '$newid'");
            
            $retval["newid"] = "$newid";
            $retval["status"] = "success";
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "Missing time or date parameter";
        }
        break;
        
    case "uploadNewInstall":
        file_put_contents("apilog.txt", $_SERVER["REMOTE_ADDR"] . ": " . date("Y-m-d H:i:s") . " got an upload new install call!!!\n", FILE_APPEND);
        $data = getPostData();
        file_put_contents("/tmp/uploadnewinstall", "json: " . print_r($data, true), FILE_APPEND);
        file_put_contents("apilog.txt", $_SERVER["REMOTE_ADDR"] . ": " . "data: " . print_r($data, true), FILE_APPEND);
        
        if (isset($_POST["time"]) && isset($_POST["date"])) {
            $time = str_pad($_POST["time"], 6, "0", STR_PAD_LEFT);
            $signdate = substr($_POST["date"], 0, 4) . "-" . substr($_POST["date"], 4, 2) . "-" . substr($_POST["date"], 6, 2) . " " . substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);

            include "handleservicecall.php";
            
            $frompending = isset($_POST["fromPending"]) && $_POST["fromPending"] == "true" ? 1 : 0;
            db_query("UPDATE appuploads SET frompending = '$frompending' WHERE id = '$newid'");
            
            if (isset($theservicecallid)) {
                db_query("UPDATE servicecall SET appid = '$newid', isdone = 1, servicecalldate = '$scdate' WHERE servicecallid = '$theservicecallid'");
            }
            
            $retval["newid"] = "$newid";
            $retval["status"] = "success";
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "Missing time or date parameter";
        }
        break;

    case "uploadServiceCall":
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . $_SERVER["REMOTE_ADDR"] . ": " . date("Y-m-d H:i:s") . " got an upload service call call!!!\n", FILE_APPEND);
        $data = getPostData();
        
        file_put_contents("/tmp/uploadservicecall", "json: " . print_r($data, true), FILE_APPEND);
        file_put_contents("apilog.txt", date("Y-m-d H:i:s") . $_SERVER["REMOTE_ADDR"] . ": " . "data: " . print_r($data, true), FILE_APPEND);
        
        if (isset($_POST["time"]) && isset($_POST["date"])) {
            $time = str_pad($_POST["time"], 6, "0", STR_PAD_LEFT);
            $signdate = substr($_POST["date"], 0, 4) . "-" . substr($_POST["date"], 4, 2) . "-" . substr($_POST["date"], 6, 2) . " " . substr($time, 0, 2) . ":" . substr($time, 2, 2) . ":" . substr($time, 4, 2);

            include "handleservicecall.php";
            file_put_contents("/tmp/uploadservicecall", "date: " . $signdate . "\n", FILE_APPEND);
            
            if (isset($theservicecallid)) {
                db_query("UPDATE servicecall SET appid = '$newid', isdone = 1, servicecalldate = '$scdate' WHERE servicecallid = '$theservicecallid'");
            }

            $frompending = isset($_POST["fromPending"]) && $_POST["fromPending"] == "true" ? 1 : 0;
            db_query("UPDATE appuploads SET frompending = '$frompending' WHERE id = '$newid'");
            
            $retval["newid"] = "$newid";
            $retval["status"] = "success";
        } else {
            $retval["status"] = "error";
            $retval["error_message"] = "Missing time or date parameter";
        }
        break;
}

file_put_contents("/tmp/rcaed", date("Y-m-d H:i:s") . " returning: " . print_r($retval, true), FILE_APPEND);

echo json_encode($retval);

$retval_str = mysql_escape_string(print_r($retval, true));
db_query("UPDATE api_calls SET retval = '$retval_str', schoolid = '$schoolid' WHERE id = '$insid'");
?>