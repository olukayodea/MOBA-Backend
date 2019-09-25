<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        $users->saveGovernmentId($_POST['id'], $_FILES['file_data']);
        header('Content-Type: application/json');
        echo "{}";
    }
?>