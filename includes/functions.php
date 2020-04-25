<?php
	session_start();
	date_default_timezone_set("Africa/Lagos");
	//error_reporting(E_ALL);
  //error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
  
  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
	
	$pageUR1      = $_SERVER["SERVER_NAME"];
  $curdomain    = str_replace("www.", "", $pageUR1);

  $local = false;

  if (($curdomain == "moba.oasmack.com/") || ($curdomain == "invapp.oasmack.com")) {
    $URL        = "https://invapp.oasmack.com/moba/";
    $servername = "192.185.189.29";
    $dbusername = "mobacom_dev";
    $dbpassword = "n%).*6CBlBBu";
    $dbname     = "mobacom_dev";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = $_SERVER['REMOTE_ADDR'];
  } else if (($curdomain == "dev.moba.com.ng/") || ($curdomain == "dev.moba.com.ng")) {
    $URL        = "https://dev.moba.com.ng/";
    $servername = "localhost";
    $dbusername = "mobacom_dev";
    $dbpassword = "n%).*6CBlBBu";
    $dbname     = "mobacom_dev";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = $_SERVER['REMOTE_ADDR'];
  } else if (($curdomain == "moba.com.ng/") || ($curdomain == "moba.com.ng")) {
    $URL        = "https://moba.com.ng/";
    $servername = "192.185.189.29";
    $dbusername = "mobacom_dev";
    $dbpassword = "n%).*6CBlBBu";
    $dbname     = "mobacom_dev";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = $_SERVER['REMOTE_ADDR'];
  } else if (($curdomain == "127.0.0.1") || ($curdomain == "localhost")) {
    $URL        = "http://127.0.0.1/MOBA-Backend/";
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "root";
    $dbname     = "MOBA_main";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = "207.35.181.162";
    $local      = true;
  } else {
    $URL        = "https://moba.com.ng/";
    $servername = "192.185.189.29";
    $dbusername = "mobacom_dev";
    $dbpassword = "n%).*6CBlBBu";
    $dbname     = "mobacom_dev";
    $replyMail  = "donotreply@moba.com.ng";
    $ip_address = $_SERVER['REMOTE_ADDR'];
  }

  //get the current server URL
  define("URL", $URL);
  define("local", $local);
  //get the database server name
  define("servername",  $servername);
  //get the database server username
  define("dbusername",  $dbusername);
  //get the database server password
  define("dbpassword",  $dbpassword);
  //get the database name
  define("dbname",  $dbname);
  define("replyMail",  $replyMail);
  define("ip_address", $ip_address);

  define("search_radius", 1000);
  define("search_radius_me", 200);

  //google APIs
  define("GoogleAPI", "AIzaSyAgGXpfWv4Be3Mwc7MwRQt9ULs73A77ryw");
  define("GoogleClientId", "81126587091-oo8egmh31gasnrq7jeof9oou8829iq3b.apps.googleusercontent.com");

  //payment gateway
  define("fl_public_key",  "FLWPUBK-c8af924e4d4f09fbd9bb6e710e95513a-X");
  define("fl_secret_key", "FLWSECK-2304d08aba0dfacd1dd55505495d6796-X");
  define("fl_encryption_key", "2304d08aba0d5fcf6044aadc");

  define("FL_charge", "https://api.ravepay.co/flwv3-pug/getpaidx/api/tokenized/charge");
  define("FL_validatecharge", "https://api.ravepay.co/flwv3-pug/getpaidx/api/validatecharge");
	define("FL_refund", "https://api.ravepay.co/gpx/merchant/transactions/refund");

  //include all the common controller methods
	include_once("controllers/mailer/class.phpmailer.php");
  include_once("controllers/common.php");
  $common   = new common;
  $common->getLocation($redirect);
  //initiate the database connection and all models
  include_once("database/main.php");
  $database = new database;
  $db       = $database->connect();

  include_once("controllers/users.php");
  include_once("controllers/posts.php");
  include_once("controllers/request.php");
  include_once("controllers/messages.php");
  include_once("controllers/responseTime.php");
  include_once("controllers/banks.php");
  include_once("controllers/category.php");
  include_once("controllers/categoryQuestion.php");
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
  include_once("controllers/currentLocation.php");
  
  $users          = new users;
  $post           = new post;
  $request        = new request;
  $messages       = new messages;
  $responseTime   = new responseTime;
  $banks          = new banks;
  $category       = new category;
  $categoryQuestion = new categoryQuestion;
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
  $currentLocation  = new currentLocation;

  include_once("views/pages/main.php");

  if (($curdomain == "dev.moba.com.ng/") || ($curdomain == "dev.moba.com.ng")) {
		$common->http2https();
  }
?>