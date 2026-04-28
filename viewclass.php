<?php

$id = $_GET["id"] ?? null;

Header( "Location: class_detail.php?id={$id}" );
exit;
?>