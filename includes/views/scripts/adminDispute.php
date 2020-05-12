<?php
    include_once("../../functions.php");
    $list = $request->getSortedList("PAUSED", "status", false, false, false, false, "ref", "DESC", "AND", false, false, "count");

    if ($list > 0) { ?>
    <span class="badge badge-danger"><?php echo $list; ?></span>
    <?php }
?>