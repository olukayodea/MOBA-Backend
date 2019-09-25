<?php
    class adminOptions extends options {
        function pageContent() {
            $service_charge = $this->get("service_charge");
            $service_charge_premium = $this->get("service_charge_premium");
            $minimum_post = $this->get("minimum_post");
            $max_days_approve = $this->get("max_days_approve");
            $mail_interval = $this->get("mail_interval");
            $result_per_page = $this->get("result_per_page");
            $ad_per_page = $this->get("ad_per_page");
            $search_per_page = $this->get("search_per_page");
            $result_per_page_mobile = $this->get("result_per_page_mobile");
            $ad_per_page_mobile = $this->get("ad_per_page_mobile");
            $search_per_page_mobile = $this->get("search_per_page_mobile");
            $text_filter = $this->get("text_filter");
            $product_ver = $this->get("product_ver");
            $tag = "Modify Settings"; ?>
  <main class="col-12" role="main">
  <form method="post" action="" enctype="multipart/form-data">
  <h2><?php echo $tag; ?></h2>
  <div class="form-group">
      <label for="name">Percentage Service Charge Regular</label>
      <input type="number" class="form-control" name="service_charge" id="service_charge" placeholder="Enter Percentage Service Charge" required value="<?php echo $service_charge; ?>">
  </div>
  <div class="form-group">
      <label for="service_charge_premium">Percentage Service Charge for Top Users</label>
      <input type="number" class="form-control" name="service_charge_premium" id="service_charge_premium" placeholder="Enter Percentage Service Charge for top Users" required value="<?php echo $service_charge_premium; ?>">
  </div>
  <div class="form-group">
      <label for="minimum_post">Minimum number of Posts till Verification  </label>
      <input type="number" class="form-control" name="minimum_post" id="minimum_post" placeholder="Minimum number of Posts till Verification" required value="<?php echo $minimum_post; ?>">
  </div>
  <div class="form-group">
      <label for="max_days_approve">Minimum number of Days till Pending Completed Jobs are Auto-approved  </label>
      <input type="number" class="form-control" name="max_days_approve" id="max_days_approve" placeholder="Minimum number of days till Pending Completed Jobs are Auto-approved" required value="<?php echo $max_days_approve; ?>">
  </div>
  <div class="form-group">
      <label for="mail_interval">Mail Interval  </label>
      <input type="number" class="form-control" name="mail_interval" id="mail_interval" placeholder="Wait time before Ereno sends reminder notifaction email" required value="<?php echo $mail_interval; ?>">
  </div>
  <div class="form-group">
      <label for="result_per_page">Result Per Page  </label>
      <input type="number" class="form-control" name="result_per_page" id="result_per_page" placeholder="number of rows to be returned per page" required value="<?php echo $result_per_page; ?>">
  </div>
  <div class="form-group">
      <label for="ad_per_page">Ad Per Page  </label>
      <input type="number" class="form-control" name="ad_per_page" id="ad_per_page" placeholder="number of ad to be returned per page" required value="<?php echo $ad_per_page; ?>">
  </div>
  <div class="form-group">
      <label for="search_per_page">Search Result Per Page  </label>
      <input type="number" class="form-control" name="search_per_page" id="search_per_page" placeholder="number of search result to be returned per page" required value="<?php echo $search_per_page; ?>">
  </div>
  <div class="form-group">
      <label for="result_per_page_mobile">Result Per Page on API </label>
      <input type="number" class="form-control" name="result_per_page_mobile" id="result_per_page_mobile" placeholder="number of rows to be returned per page on API" required value="<?php echo $result_per_page_mobile; ?>">
  </div>
  <div class="form-group">
      <label for="ad_per_page_mobile">Ad Per Page on API </label>
      <input type="number" class="form-control" name="ad_per_page_mobile" id="ad_per_page_mobile" placeholder="number of ad to be returned per page on API" required value="<?php echo $ad_per_page_mobile; ?>">
  </div>
  <div class="form-group">
      <label for="search_per_page_mobile">Search Result Per Page on API </label>
      <input type="number" class="form-control" name="search_per_page_mobile" id="search_per_page_mobile" placeholder="number of search result to be returned per page on API" required value="<?php echo $search_per_page_mobile; ?>">
  </div>
  <div class="form-group">
      <label for="product_ver">API Version  </label>
      <input type="number" class="form-control" name="product_ver" id="product_ver" placeholder="Version number of the current mobile app API" required value="<?php echo $product_ver; ?>">
  </div>
  <div class="form-group">
      <label for="text_filter">Muted Words  </label>
      <input type="text" class="form-control" name="text_filter" id="text_filter" placeholder="Enter mutted words here " value="<?php echo $text_filter; ?>" />
      <small id="text_filter_help" class="form-text text-muted">List of words and tags that will mutted in all conversations seperated by corma (,).</small>
  </div>
  <button type="submit" name="submitOption" class="btn btn-primary"><?php echo $tag; ?></button>
  </form>
</main>
</div>
<link rel="stylesheet" href="<?php echo URL; ?>css/tagify.css">
<script src="<?php echo URL; ?>js/jQuery.tagify.min.js"></script>
<script type="text/javascript">
$('[name=text_filter]').tagify({duplicates : false});
</script>
        <?php }

        function postMew($name, $value) {
            $add = $this->add($name, $value);

            if ($add) {
                return $add;
            } else {
                return false;
            }
        }
    }
?>