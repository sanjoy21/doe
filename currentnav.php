<?php

$specialtnav_safe = $specialtnav ?? false;
$tid_safe = $tid ?? null;
$session_id_safe = $session_id ?? null;
$currentusertype_safe = $currentusertype ?? '';
$nologinrequired_safe = $nologinrequired ?? false;
$noleftnav_safe = $noleftnav ?? false;

if( $specialtnav_safe ) 
{
    if( $tid_safe ) {
        if( $session_id_safe == $tid_safe ) { 
            include "ssi/leftnav_trainers.php";
        } else { 
            include "ssi/leftnav_doe.php"; 
        }
    }
    else
    {
        include "ssi/leftnav_trainers_blank.php"; 
    }
    
}
else if( !$currentusertype_safe || $nologinrequired_safe || $noleftnav_safe )
{
    include "ssi/leftnav2.php";
}
else if( $session_id_safe && $currentusertype_safe != "trainer" )
{
    include "ssi/leftnav_doe.php"; 
}
else if( $session_id_safe && $currentusertype_safe == "trainer" ) { 
    include "ssi/leftnav_trainers.php"; 
}
else
{
    // Default case: do nothing
}
?>