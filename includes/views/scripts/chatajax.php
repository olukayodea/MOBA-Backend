<?php
include_once("../../functions.php");
if ($_POST) {
    $array['message']  = $_POST['content'];
    $array['user_r_id']  = $_POST['user_r_id'];
    $array['user_id']  = $_POST['user_id'];
    $array['post_id']  = $_POST['post_id'];
    $array['m_type']  = $_POST['m_type'];
    $id = $messages->add($array);

    $data["to"] = $_POST['user_r_id'];
    $data["title"] = "Message Notification";
    $data["body"] = "New message from ".$users->listOnValue($_POST['user_id'], "screen_name");
    $data['data']['page_name'] = "messages";
    $data['data']['provider']['ref'] = $_POST['user_r_id'];
    $data['data']['provider']['screen_name'] = $users->listOnValue( $_POST['user_r_id'], "screen_name" );
    $data['data']['postId'] = $_POST['post_id'];

    $notifications->sendPush($data);
}
?>
<li class="media" id='<?php echo $id; ?>'>
    <?php $users->getProfileImage($array['user_id'], "", false); ?>
    <div class="media-body">
        <small class="time"><i class="fa fa-clock-o"></i> <?php echo $common->get_time_stamp(time()); ?></small>
        <p class="mt-0">
            <?php echo $array['message']; ?>
        </p>
    </div>
</li>