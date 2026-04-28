<?php
require_once('mysql.php');
$crow = getClassRow($id);
$corow = getCompanyRow($crow["companyid"]);

if ($calcnames) {
    if ($corow["iscorp"]) {
        $names = false;
    } else {
        $names = true;
    }
}

if ($names) {
    include "roster_print_names_include.php";
} else {
    include "roster_print_include.php";
}

$id = $corow["id"];
include "printaedsign.php";
?>