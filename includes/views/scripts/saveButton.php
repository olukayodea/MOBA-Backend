<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        echo $project_save->manage($_POST);
    }
?>