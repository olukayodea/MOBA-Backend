<?php
include_once("header.php");
include_once("adminBanks.php");
include_once("adminCategory.php");
include_once("adminCountry.php");
include_once("adminOptions.php");
include_once("adminUsers.php");
include_once("adminTransactions.php");
include_once("adminPayments.php");
include_once("adminCards.php");
include_once("adminAccounts.php");
include_once("adminAdvert.php");
include_once("userHome.php");
include_once("userPostedAds.php");
include_once("userProject.php");
include_once("userPayment.php");
include_once("userBankAccount.php");
include_once("adminRating.php");
include_once("inboxPages.php");
$pageHeader         = new pageHeader;
$adminBanks         = new adminBanks;
$adminCategory      = new adminCategory;
$adminCountry       = new adminCountry;
$adminOptions       = new adminOptions;
$adminUsers         = new adminUsers;
$adminTransactions  = new adminTransactions;
$adminPayments      = new adminPayments;
$adminCards         = new adminCards;
$adminAccounts      = new adminAccounts;
$adminAdvert        = new adminAdvert;
$userHome           = new userHome;
$userProject        = new userProject;
$userPostedAds      = new userPostedAds;
$userBankAccount    = new userBankAccount;
$userPayment        = new userPayment;
$adminRating        = new adminRating;
$inboxPages         = new inboxPages;
?>