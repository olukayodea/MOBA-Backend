<?php
    include_once("includes/functions.php");
    $notifications->senemail();
    $query['data'] = "message";
    $database->run("cron", $query);
    echo "";
?>