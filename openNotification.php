<?php
    include_once("includes/functions.php");
    if (isset($_REQUEST['ref'])) {
        $ref = $_REQUEST['ref'];
    } else {
        header("location: ./");
    }

    $data = $notifications->listOne($ref);

    if ($data['event'] == "post_messages") {
        $notifications->markReadOne($ref);
        header("location: ".$common->seo($data['event_id'], "view")."#messages");
    }
?>