<?php
    $redirect = "hire";
    include_once("includes/functions.php");
    include_once("includes/sessionUser.php");
    if (isset($_REQUEST['ref'])) {
        $project_id = $_REQUEST['ref'];
    } else {
        header("location: ./?error=".urlencode("No Ad to Post at this monment"));
    }
    if (isset($_REQUEST['edit'])) {
        $edit = $_REQUEST['edit'];
        if ($edit > 0) {
            $tag = "&done=".urlencode("Ad updated successfully");
        } else {
            $tag = "";
        }
    }

    $data = $projects->listOne($project_id);

    if ($data['project_type'] == "client") {
        if ($data['payment_status'] == "2") {
            header("location: confirmation/".base64_encode($project_id).$tag);
        } else {
            header("location: payments/Authorization/".base64_encode($project_id));
        }
    } else {
        header("location: confirmation/".base64_encode($project_id));
    }
?>