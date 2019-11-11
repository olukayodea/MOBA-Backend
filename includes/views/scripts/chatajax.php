<?php
include_once("../../functions.php");
if ($_POST) {
    $array['message']  = $_POST['content'];
    $array['user_r_id']  = $_POST['user_r_id'];
    $array['user_id']  = $_POST['user_id'];
    $array['post_id']  = $_POST['post_id'];
    $array['m_type']  = $_POST['m_type'];
    $id = $messages->add($array);
}
?>
<li class="media" id='<?php echo $id; ?>'>
    <?php $users->getProfileImage($array['user_id'], "media-object", "25"); ?>
    <div class="media-body">
        <small class="time"><i class="fa fa-clock-o"></i> <?php echo $common->get_time_stamp(time()); ?></small>
        <p class="mt-0">
            <?php echo $array['message']; ?>
        </p>
    </div>
</li>