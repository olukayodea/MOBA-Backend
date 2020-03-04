<?php
	session_start();
	date_default_timezone_set("Africa/Lagos");
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
	
	$pageUR1      = $_SERVER["SERVER_NAME"];
  $curdomain    = str_replace("www.", "", $pageUR1);

  if (($curdomain == "invapp.oasmack.com/") || ($curdomain == "invapp.oasmack.com")) {
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
  } else if (($curdomain == "127.0.0.1") || ($curdomain == "localhost")) {
    $URL        = "http://127.0.0.1/MOBA-Backend/";
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "root";
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
  define("URL", $URL);
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
  define("fl_public_key",  "FLWPUBK_TEST-ffbf9b7c7fe3fcc61926f90c91f4fab9-X");
  define("fl_secret_key", "FLWSECK_TEST-1cb22d96acf6436408173a882d4b1942-X");
  define("fl_encryption_key", "FLWSECK_TEST53b6175abd98");

  define("FL_charge", "https://api.ravepay.co/flwv3-pug/getpaidx/api/tokenized/charge");
	define("FL_validatecharge", "https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/validatecharge");
	define("FL_refund", "https://ravesandboxapi.flutterwave.com/gpx/merchant/transactions/refund");

  //include all the common controller methods
  include_once("controllers/common.php");
  $common   = new common;
  $common->getLocation();
  //initiate the database connection and all models
  include_once("database/main.php");
  $database = new database;
  $db       = $database->connect();

  include_once("controllers/users.php");
  include_once("controllers/posts.php");
  include_once("controllers/request.php");
  include_once("controllers/messages.php");
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
  
  $users          = new users;
  $post           = new post;
  $request        = new request;
  $messages       = new messages;
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

  include_once("views/pages/main.php");

  if (($curdomain == "dev.moba.com.ng/") || ($curdomain == "dev.moba.com.ng")) {
		$common->http2https();
  }
?>