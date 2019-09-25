<?php
    include_once("../../functions.php");
    $s = $_REQUEST['q'];

    $data = $users->usersMailSearch($s);

    header('Content-Type: application/json');
    echo json_encode($data);
?>