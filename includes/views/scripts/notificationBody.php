<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        $notificationList = $notifications->getSortedList($_POST['user'], "user_id", false, false, false, false, "ref", "DESC", "AND", false, 20); 
        $messageCount = $inbox->getSortedList($_POST['user'], "to_id", "status", "SENT", "read", 0, "ref", "DESC", "AND", false, false, "count"); 

        if (count($notificationList) > 0) { ?>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <?php if ($messageCount > 0) { ?>
                <a class="dropdown-item" href="<?php echo URL."inbox"; ?>"><?php echo $common->faIcons("messages"); ?>You have <?php echo $messageCount. " unread ".$common->addS("message", $messageCount)." in your inbox"; ?></a>
                <div class="dropdown-divider"></div>
                <?php } ?>
                <?php for ($i = 0; $i < count($notificationList); $i++) { ?>
                <a class="dropdown-item<?php if ($notificationList[$i]['status'] == 0) { ?> active<?php } ?>" href="<?php echo URL."openNotification?ref=".$notificationList[$i]['ref']; ?>"><?php echo $common->faIcons($notificationList[$i]['event']); ?> <?php echo $notificationList[$i]['message']; ?></a>
                <div class="dropdown-divider"></div>
                <?php } ?>
                <a class="dropdown-item" href="<?php echo URL."notifications"; ?>"><i class="fa fa-bell"></i> View all in Notification</a>
            </div>
        <?php }
    }
?>