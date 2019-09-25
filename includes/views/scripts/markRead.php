<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        $messages->markRead($_POST['user_id'], $_POST['project_id']);
    }
?>