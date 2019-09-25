<?php
    include_once("../../functions.php");
    $list = $users->getSortedList("1", "verified", false, false, false, false, "ref", "ASC", "AND", false, false, "count");

    if ($list > 0) { ?>
    <span class="badge badge-danger"><?php echo $list; ?></span>
    <?php }
?>