<?php
	session_start();
	date_default_timezone_set("America/Toronto");
	
	$pageUR1      = $_SERVER["SERVER_NAME"];
  $curdomain    = str_replace("www.", "", $pageUR1);

  if (($curdomain == "dev.moba.com.ng/") || ($curdomain == "dev.moba.com.ng")) {
    $URL        = "https://dev.moba.com.ng/";
    $servername = "localhost";
    $dbusername = "mobacom_dev";
    $dbpassword = "n%).*6CBlBBu";
    $dbname     = "mobacom_dev";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = $_SERVER['REMOTE_ADDR'];
  } else if (($curdomain == "127.0.0.1") || ($curdomain == "localhost")) {
    $URL        = "http://127.0.0.1/MOBA/";
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "mysql";
    $dbname     = "MOBA_main";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = "207.35.181.162";
  } else {
    $URL        = "https://dev.moba.com.ng/";
    $servername = "localhost";
    $dbusername = "mobacom_dev";
    $dbpassword = "n%).*6CBlBBu";
    $dbname     = "mobacom_dev";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = $_SERVER['REMOTE_ADDR'];
  }

  //get the current server URL
  define("URL", $URL, true);
  //get the database server name
  define("servername",  $servername, true);
  //get the database server username
  define("dbusername",  $dbusername, true);
  //get the database server password
  define("dbpassword",  $dbpassword, true);
  //get the database name
  define("dbname",  $dbname, true);
  define("replyMail",  $replyMail, true);
  define("ip_address", $ip_address, true);

  define("search_radius", 0.5, true);
  define("search_radius_me", 0.02, true);

  //google APIs
  define("GoogleAPI", "AIzaSyAMCoTIoeIbfVpr5jwfTUw_jEHmZjmU8CY");
  define("GoogleClientId", "81126587091-oo8egmh31gasnrq7jeof9oou8829iq3b.apps.googleusercontent.com");
  define("GoogleSecret", "2pV-S4uN-7sdz9-LmeGYMiOW");

  //payment gateway
  define("merchID",  "300206916", true);
  define("gateway_access_code", "0bcd3711b13744D5A5E8608ca9A5F7B0");
  define("batch_gateway_access_code", "892e267e758041Cf9c5Cef7907C95E48");
  define("gateway_passcode", base64_encode(merchID.":".gateway_access_code));

  //include all the common controller methods
  include_once("controllers/common.php");
  $common   = new common;
  $common->getLocation();
  //initiate the database connection and all models
  include_once("database/main.php");
  $database = new database;
  $db       = $database->connect();

  include_once("controllers/users.php");
  include_once("controllers/projects.php");
  include_once("controllers/projects_data.php");
  include_once("controllers/projects_negotiate.php");
  include_once("controllers/project_save.php");
  include_once("controllers/messages.php");
  include_once("controllers/banks.php");
  include_once("controllers/category.php");
  include_once("controllers/country.php");
  include_once("controllers/identity.php");
  include_once("controllers/options.php");
  include_once("controllers/alerts.php");
  include_once("controllers/payment_card.php");
  include_once("controllers/bank_account.php");
  include_once("controllers/payments.php");
  include_once("controllers/transactions.php");
  include_once("controllers/media.php");
  include_once("controllers/search.php");
  include_once("controllers/rating.php");
  include_once("controllers/rating_comment.php");
  include_once("controllers/rating.question.php");
  include_once("controllers/notifications.php");
  include_once("controllers/wallet.php");
  include_once("controllers/inbox.php");
  include_once("controllers/api.php");
  
  $users          = new users;
  $projects       = new projects;
  $projects_data  = new projects_data;
  $projects_negotiate = new projects_negotiate;
  $project_save   = new project_save;
  $messages       = new messages;
  $banks          = new banks;
  $category       = new category;
  $options        = new options;
  $country        = new country;
  $identity       = new identity;
  $alerts         = new alerts;
  $media          = new media;
  $bank_account   = new bank_account;
  $payments       = new payments;
  $transactions   = new transactions;
  $search         = new search;
  $rating         = new rating;
  $rating_question = new rating_question;
  $rating_comment = new rating_comment;
  $notifications  = new notifications;
  $wallet         = new wallet;
  $inbox          = new inbox;
  $api            = new api;

  include_once("views/pages/main.php");

  if (($curdomain == "dev.moba.com.ng/") || ($curdomain == "dev.moba.com.ng")) {
		$common->http2https();
  }

  if (isset($_REQUEST['filter'])) {
    if ($_REQUEST['filter'] == "all") {
      unset($_SESSION['filter']);
    } else {
      $_SESSION['filter'] = $_REQUEST['filter'];
    }

    header("location: ".$_SERVER['HTTP_REFERER'] );
  }
?>