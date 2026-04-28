<?php

require_once 'Mobile-Detect/Mobile_Detect.php';
$detect = new Mobile_Detect;

$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'computer' : 'phone') : 'computer');
$usemobiletop = '';
$mobile = '';
if( ( $usemobiletop && $deviceType == "phone" ) || $mobile )
include "ssi/phonetop.php";
else
    include "ssi/newtop.php";
?>