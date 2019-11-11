<?php
include_once("../../functions.php");
if ($_GET['post_id']) {
    $post_id=$_GET['post_id'];
    $user_id=$_GET['user_id'];
    $user_r_id=$_GET['user_r_id'];

    $row    = $messages->getLast($post_id, $user_id, $user_r_id);
    $u      = $row['user_id'];
    $proj   = $row['post_id'];
    $id     = $row['ref'];
    $msg    = $row['message'];
    $m_type = $row['m_type'];
    $m_data = explode("_", $row['m_type_data']);
    $time   = $common->get_time_stamp(strtotime($row['create_time']));

    header('Content-Type: application/json');
    if ($u != $user_id) {
    echo '{"posts": [';
    echo '
        {
        "id":"'.$id.'",
        "url":"'.$common->seo($proj, "view").'",
        "name":"'.$users->listOnValue($u, "screen_name").'",
        "user":"'.$u.'",
        "time":"'.$time.'",
        "m_type":"'.$m_type.'",
        "msg":"'.$msg.'",
        "data_1":"'.@$m_data[0].'",
        "data_2":"'.@$m_data[1].'"
        }';	
    echo ']}';
    }
}
?>