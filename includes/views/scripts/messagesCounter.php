<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        $notificationListNew =  $inbox->getSortedList($_POST['user'], "to_id", "status", "SENT", "read", 0, "ref", "DESC", "AND", false, false, "count");

        if ($notificationListNew > 0) { ?>
        <span class="badge badge-danger"><?php echo $notificationListNew; ?></span>
        <?php }
    }
?>