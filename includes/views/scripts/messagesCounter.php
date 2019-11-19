<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        $notificationListNew = $notifications->getCount($_POST['user'], "post_messages");
        
        if ($notificationListNew > 0) { ?>
        <span class="badge badge-danger"><?php echo $notificationListNew; ?></span>
        <?php }
    }
?>