<?php
    include_once("../../functions.php");
    if (isset($_POST)) {
        $notificationListNew = $notifications->getCount($_POST['request']);

        $addressData['latitude'] = $_POST['latitude'];
        $addressData['longitude'] = $_POST['longitude'];
        $addressData['state_code'] = $_POST['state_code'];
        $addressData['state'] = $_POST['state'];
        $addressData['code'] = $_POST['code'];
        $addressData['country'] = $_POST['country'];

        $result = $request_accept->getResponse($_POST['request']); ?>

        <h5>Request Response for '<strong><?php echo $_POST['category_title']; ?></strong></h5>
        <div class="moba-line mb-3"></div>
        <p>Your request for <strong><?php echo $_POST['category_title']; ?></strong> at <strong><?php echo $_POST['address']; ?></strong> has <strong><?php echo intval(count($result)); ?></strong> <?php echo $common->addS("response", intval(count($result))); ?></p>
        <div class="row">
            <?php if (count($result) > 0) { ?>
                <?php for ($i = 0; $i < count($result); $i++) {
                    $mapData = $common->googleDirection($addressData, $result[$i]); ?>
                    <div class="col-lg-4">
                        <div class="moba-content__img">
                            <div class="row">
                                <div class="col-lg-4">
                                <?php echo $users->getProfileImage($result[$i]['ref'], "mr-3 float-left", 250); ?>
                                </div>
                                <div class="col-lg-7">
                                <?php echo $result[$i]['screen_name']; ?><br>
                                <small><i class="fas fa-user-alt"></i>&nbsp;<?php echo $result[$i]['about_me']; ?></small><br>
                                <small><i class="fas fa-road"></i>&nbsp;<?php echo $mapData['distance']['text'] ?></small><br>
                                <small><i class="fas fa-clock"></i>&nbsp;<?php echo $mapData['duration']['text'] ?></small><br>
                                <?php echo $rating->drawRate(intval($rating->getRate($result[$i]['ref']))); ?><br>
                                <a href="<?php echo $common->seo($result[$i]['ref'], "profile")."/".$_POST['request']."/view"; ?>" title="View Profile" class="btn purple-bn-small pd"><i class="fas fa-eye"></i></a>&nbsp;<a href="<?php echo $common->seo($result[$i]['ref'], "profile")."/".$_POST['request']."/message"; ?>" title="Message" class="btn purple-bn-small pd"><i class="fas fa-comments"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="moba-line m-3"></div>
        <small>You saved payment method will be pre-authorized the total amount for the call out charge, if you cancel this request, the funds will be returned back to your wallet</small>
    <?php }
?>