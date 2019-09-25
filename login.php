<?php
  include_once("includes/functions.php");
	
	if ((isset($_REQUEST['redirect'])) && ($_REQUEST['redirect'] != "")) {
		$redirect = $_REQUEST['redirect'];
	} else {
		$redirect = "./";
	}
  $urlParam = $common->getParam($_SERVER['REQUEST_URI']);
  $tagLink = $redirect."?".$urlParam;
  
  if (isset($_REQUEST['logout'])) {
    $users->logout();
    header("location: ./");
  } else if (isset($_REQUEST['recover'])) {
    unset($_SESSION['users']);
    $token = $_REQUEST['token'];
    $firstSplit = explode("_", $token);
    if ($firstSplit[2] > time()) {
      $build = sha1($firstSplit[0]."_".$firstSplit[3]."_".$firstSplit[0]);
      if ($build != $firstSplit[1]) {
        header("location: ?password&error=".urlencode("This link is invalid"));
      }
    } else {
      header("location: ?password&error=".urlencode("The password recovery link you clicked on has expired"));
    }
  }
  if (isset($_POST['changePassword'])) {
    $add = $users->changePassword($_POST);
    if ($add) {
      header("location: ".URL."login?done=".urlencode("Password Reset Successful. You can now login"));
    } else {
      header("location: ?error=".urlencode("An error occured while changing your password. Your password has not being changed, please try again"));
    }
  } else if (isset($_POST['verify'])) {
    $add = $users->validateAcc($_POST['email']);
    if ($add) {
      header("location: ?password&done=".urlencode("A link has been sent to ".$_POST['email'].". Click on the Link to continue. This Link expires in 24 hours. Ignore the email if you change your mind at any time.!"));
    } else {
      header("location: ?password&error=".urlencode($_POST['email']." does not look like a valid email in our database!"));
    }
  } else if (isset($_POST['register'])) {
    if (strtolower($_POST['captcha']) == strtolower($_SESSION['code'])) {
      unset($_POST['confirm_password']);
      unset($_POST['register']);
      $names = explode(" ", $_POST['last_name']);
      $_POST['last_name'] = $names[0];
      unset($names[0]);
      $_POST['other_names'] = trim(implode(" ", $names));
      $key = strtolower(substr($_POST['other_names'], 0, 6));
      $_POST['screen_name'] = $users->confirmUnique($users->createUnique($key));
      unset($_POST['captcha']);
      $addUrser = $users->create($_POST);
      if ($addUrser) {

          if ($addUrser == "error") {
              header("location: ?join&redirect=".$redirect."&error=".urlencode("there was an error creating this account, please try again"));
          } else if ($addUrser == "duplicate data") {
              header("location: ?join&redirect=".$redirect."&error=".urlencode("this account already exist, please login or reset your password to continue"));
          } else {
              header("location: ".$tagLink."&done=".urldecode("account was created successfully"));
          
          }
      } else {
          header("location: ?join&redirect=".$redirect."&error=".urlencode("there was an error creating account"));
      }
    } else {
      $errorMessage = "The seciurity code you entered does not match";
    }
  } else if (isset($_POST['login'])) {
      $login = $users->login($_POST);
      if ($login) {
          header("location: ".$tagLink);
      } else {
          header("location: ?error=".urlencode("email and password combination is not correct"));
      }
  } else if (isset($_SESSION['users'])) {
      header("location: ./");
  }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<?php $pageHeader->headerFiles(); ?>
<title>Login</title>
<link href="SpryAssets/SpryValidationConfirm.css" rel="stylesheet" type="text/css">
<script src="SpryAssets/SpryValidationConfirm.js" type="text/javascript"></script>
</head>

<body>
<?php $pageHeader->loginStrip(true, true); ?>
<?php $pageHeader->navigation(); ?>
<?php if (isset($_REQUEST['recover'])) { ?>
<form name="form3" method="post" action="">
  <h2>Recover Password</h2>
  
  <div class="form-group">
    <label for="password2">Password</label>
    <span id="sprypassword1">
      <input type="password" class="form-control" id="password3" name="password" aria-describedby="passwordHelp" placeholder="Enter your Password">
      <span class="passwordRequiredMsg">A value is required.</span>
      <span class="passwordMinCharsMsg">Minimum number of characters not met.</span>
      <span class="passwordInvalidStrengthMsg">The password doesn't meet the specified strength.</span>
    </span>
    <small id="passwordHelp" class="form-text text-muted">Please make sure your password contains an UPPERCASE letter, a number and a minimum of 6 characters.</small>
    <div id="pswd_info">
      Password requirements:
      <ul>
        <li id="letter" class="invalid">At least <strong>one letter</strong></li>
        <li id="capital" class="invalid">At least <strong>one capital letter</strong></li>
        <li id="number" class="invalid">At least <strong>one number</strong></li>
        <li id="length" class="invalid">Be at least <strong>6 characters</strong></li>
      </ul>
    </div>
  </div>
  <div class="form-group">
    <label for="confirm_password2">Confirm Password</label>
    <span id="spryconfirm2">
      <input type="password" class="form-control" id="confirm_password2" name="confirm_password2" aria-describedby="confirmHelp" placeholder="Enter confirm your password">
      <span class="confirmRequiredMsg">A value is required.</span>
      <span class="confirmInvalidMsg">The values don't match.</span>
    </span>
    <small id="confirmHelp" class="form-text text-muted">Please enter your password again.</small>
  </div>
  
  <button type="submit" name="changePassword" id="changePassword" disabled class="btn btn-primary">Change Password</button>
</form>
<?php } else if (isset($_REQUEST['recoverPassword'])) { ?>
<form name="form2" method="post" action="">
  <h2>Recover Password</h2>
  <div class="form-group">
    <label for="email">Email address</label>
    <span id="sprytextfield7">
      <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email']; ?>" aria-describedby="emailHelp" placeholder="Enter your email">
      <span class="textfieldRequiredMsg">A value is required.</span>
      <span class="textfieldInvalidFormatMsg">Invalid format.</span>
    </span>
  </div>
  <button type="submit" name="verify" id="verify" class="btn btn-primary">Verify Account E-Mail</button>
</form>
<?php } else if (isset($_REQUEST['join'])) { ?>
<form name="form1" method="post" action="">
  <h2>Register</h2>
  <div class="form-group">
    <label for="email">Email address</label>
    <span id="sprytextfield3">
      <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email']; ?>" aria-describedby="emailHelp" placeholder="Enter your email">
      <span class="textfieldRequiredMsg">A value is required.</span>
      <span class="textfieldInvalidFormatMsg">Invalid format.</span>
    </span>
    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
  </div>
  <div class="form-group">
    <label for="last_name">Full Names</label>
    <span id="sprytextfield1">
      <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $_POST['last_name']; ?>" aria-describedby="lastnameHelp" placeholder="Enter your Last name and First name">
      <span class="textfieldRequiredMsg">A value is required.</span>
    </span>
    <small id="lastnameHelp" class="form-text text-muted">Please enter your first name and last name as it appears on your ID.</small>
  </div>
  <div class="form-group">
    <label for="password2">Password</label>
    <span id="sprypassword1">
      <input type="password" class="form-control" id="password2" name="password" aria-describedby="passwordHelp" placeholder="Enter your Password">
      <span class="passwordRequiredMsg">A value is required.</span>
      <span class="passwordMinCharsMsg">Minimum number of characters not met.</span>
      <span class="passwordInvalidStrengthMsg">The password doesn't meet the specified strength.</span>
    </span>
    <small id="passwordHelp" class="form-text text-muted">Please make sure your password contains an UPPERCASE letter, a number and a minimum of 6 characters.</small>
    <div id="pswd_info">
      Password requirements:
      <ul>
        <li id="letter" class="invalid">At least <strong>one letter</strong></li>
        <li id="capital" class="invalid">At least <strong>one capital letter</strong></li>
        <li id="number" class="invalid">At least <strong>one number</strong></li>
        <li id="length" class="invalid">Be at least <strong>6 characters</strong></li>
      </ul>
    </div>
  </div>
  <div class="form-group">
    <label for="confirm_password">Confirm Password</label>
    <span id="spryconfirm1">
      <input type="password" class="form-control" id="confirm_password" name="confirm_password" aria-describedby="confirmHelp" placeholder="Enter confirm your password">
      <span class="confirmRequiredMsg">A value is required.</span>
      <span class="confirmInvalidMsg">The values don't match.</span>
    </span>
    <small id="confirmHelp" class="form-text text-muted">Please enter your password again.</small>
  </div>
  <div class="form-group">
    <label for="captcha">Text Captcha</label>
    <img src="<?php echo URL; ?>includes\views\scripts\captcha" />
    
    <span id="sprytextfield0">
      <input type="text" name="captcha" id="captcha" class="form-control" required />
      <span class="textfieldRequiredMsg">A value is required.</span>
    </span>
    <small id="confirmHelp" class="form-text text-muted">Please enter the text in the image above.</small>
  </div>
  <button type="submit" name="register" id="register" disabled class="btn btn-primary">Register</button>
  <div class="form-group">
    <div class="form-group col-md-2">
      <div class="g-signin2" data-onsuccess="onSignIn"></div>
      <div class="fb-login-button" data-width="" data-size="medium" data-scope="public_profile,email" data-onlogin="checkLoginState();" data-button-type="continue_with" data-auto-logout-link="false" data-use-continue-as="false"></div>
    </div>
  </div>
  <div class="form-group">
    <a href='login'>Login</a>
  </div>
</form>
<?php } else { ?>
<form name="form1" method="post" action="">
<h2>Login</h2>
<div class="form-group">
  <label for="email">Email address</label>
  <span id="sprytextfield5">
      <input type="text" name="email" id="email" required class="form-control">
      <span class="textfieldRequiredMsg">A value is required.</span>
      <span class="textfieldInvalidFormatMsg">Invalid Email.</span>
  </span>
</div>
<div class="form-group">
  <label for="email">Password</label>
  <span id="sprytextfield6">
      <input type="password" name="password" id="password" required class="form-control">
      <span class="textfieldRequiredMsg">A value is required.</span>
  </span>
</div>
  <button type="submit" name="login" id="login" class="btn btn-primary">Login</button>
  <div class="form-group">
    <a href='<?php echo URL; ?>login?recoverPassword'>Forgot Password</a> | <a href='<?php echo URL; ?>login?join'>Register</a>
  </div>
  <div class="form-group">
    <div class="g-signin2" data-onsuccess="onSignIn"></div>
    <div class="fb-login-button" data-width="" data-size="medium" data-scope="public_profile,email" data-onlogin="checkLoginState();" data-button-type="login_with" data-auto-logout-link="false" data-use-continue-as="false"></div>
  </div>
</form>
<?php } ?>
<?php $pageHeader->jsFooter(); ?>
<script src="https://apis.google.com/js/platform.js" async defer></script>
<script type="text/javascript">
function onSignIn(googleUser) {
  var profile = googleUser.getBasicProfile();
  var name = profile.getName();
  var image = profile.getImageUrl();
  var email = profile.getEmail();
  var otherdata = "<?php echo urldecode($tagLink); ?>";
  var string = btoa(name+"+"+image+"+"+email+"+"+otherdata);
  location='<?php echo URL."auth"; ?>?social_media&data='+string;
}
var sprytextfield0 = new Spry.Widget.ValidationTextField("sprytextfield0");
var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1");
var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2");
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email");
var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7", "email");
var sprypassword1 = new Spry.Widget.ValidationPassword("sprypassword1", {minChars:6, minAlphaChars:1, minNumbers:1, minUpperAlphaChars:1, validateOn:["change"]});
<?php if (isset($_REQUEST['recover'])) { ?>
  var spryconfirm2 = new Spry.Widget.ValidationConfirm("spryconfirm2", "password3");
<?php } ?>
<?php if (isset($_REQUEST['join'])) { ?>
var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryconfirm1", "password2");

<?php } ?>
var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "email");
var sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6");

function checkLoginState() {
  FB.getLoginStatus(function(response) {
    //statusChangeCallback(response);
    console.log(response);
    if (response.status == "connected") {
      FB.api('/me', {fields: 'name, email, picture'}, function(response) {
        var name = response.name;
        var image = response.picture.data.url;
        var email = response.email;
        var otherdata = "<?php echo urldecode($tagLink); ?>";
        var string = btoa(name+"+"+image+"+"+email+"+"+otherdata);
        location='<?php echo URL."auth"; ?>?social_media&data='+string;
     });
    }
  });
}

$(document).ready(function() {
  $('#password2, #password3').keyup(function() {
    // keyup code here
    var pswd = $(this).val();
    var c_lenght = false;
    var c_char = false;
    var c_upper = false;
    var c_number = false;
    //validate the length
    if ( pswd.length < 6 ) {
      $('#length').removeClass('valid').addClass('invalid');
      c_lenght = false;
    } else {
      $('#length').removeClass('invalid').addClass('valid');
      c_lenght = true;
    }

    //validate letter
    if ( pswd.match(/[A-Za-z]/) ) {
      $('#letter').removeClass('invalid').addClass('valid');
      c_char = true;
    } else {
      $('#letter').removeClass('valid').addClass('invalid');
      c_char = false;
    }

    //validate capital letter
    if ( pswd.match(/[A-Z]/) ) {
      $('#capital').removeClass('invalid').addClass('valid');
      c_upper = true;
    } else {
      $('#capital').removeClass('valid').addClass('invalid');
      c_upper = false;
    }

    //validate number
    if ( pswd.match(/\d/) ) {
      $('#number').removeClass('invalid').addClass('valid');
      c_number = true;
    } else {
      $('#number').removeClass('valid').addClass('invalid');
      c_number = false;
    }

    if ((c_number == true) && (c_upper == true) && (c_char == true) && (c_lenght == true)) {
      $('button[type=submit]').prop( "disabled", false );
    } else {
      $('button[type=submit]').prop( "disabled", true );
    }
  });
});
</script>
</body>
</html>