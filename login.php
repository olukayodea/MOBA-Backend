<?php
  include_once("includes/functions.php");
  $loc = $country->getLoc($_SESSION['location']['code']);
  $list = $category->categoryList($loc['ref'], 0);
  $listID = $identity->getSortedList("ACTIVE", 'status', "country", $loc['ref']);
	
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
      header("location: ?error=".urlencode("An error occured while changing your password. Your password has not been changed, please try again"));
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
      unset($_POST['captcha']);
      $addUrser = $users->create($_POST, $_FILES);
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


<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Moba - Find the best artisans</title>
    <?php $pageHeader->headerFiles(); ?>
	</head>

  <body class="moba-login">

		<div class="container">
				<div class="row justify-content-center align-items-center moba-login-h" >
					<div class="moba-login-w" >
						<div class="card mx-4">
							<div class="card-body px-4">
              <?php $pageHeader->navigation(); ?>
              <?php if (isset($_REQUEST['recover'])) { ?>
              <form name="form3" method="post" action="" autocomplete="off">
              <a href="<?php echo URL; ?>"><img src="<?php echo URL; ?>images/logo.png" class="mb-3" width="70"></a>
                <h2>Recover Password</h2>
                
                <div class="form-group">
                  <span id="sprypassword1">
                    <input type="password" class="form-control" id="password3" name="password" aria-describedby="passwordHelp" placeholder="Password">
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
                  <span id="spryconfirm2">
                    <input type="password" class="form-control" id="confirm_password2" name="confirm_password2" aria-describedby="confirmHelp" placeholder="Confirm Password">
                    <span class="confirmRequiredMsg">A value is required.</span>
                    <span class="confirmInvalidMsg">The values don't match.</span>
                  </span>
                  <small id="confirmHelp" class="form-text text-muted">Please enter your password again.</small>
                </div>
                <input type="hidden" class="form-control" id="ref" name="ref" value="<?php echo $firstSplit[3]; ?>">
                <button type="submit" name="changePassword" id="changePassword" disabled class="btn purple-bn">Change Password</button>
              </form>
              <?php } else if (isset($_REQUEST['recoverPassword'])) { ?>
              <form name="form2" method="post" action="">
              <a href="<?php echo URL; ?>"><img src="<?php echo URL; ?>images/logo.png" class="mb-3" width="70"></a>
                <h2>Recover Password</h2>
                <div class="form-group">
                  <span id="sprytextfield7">
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email']; ?>" aria-describedby="emailHelp" placeholder="Email Address">
                    <span class="textfieldRequiredMsg">A value is required.</span>
                    <span class="textfieldInvalidFormatMsg">Invalid format.</span>
                  </span>
                </div>
                <button type="submit" name="verify" id="verify" class="btn purple-bn">Verify Account E-Mail</button>
              </form>
              <?php } else if (isset($_REQUEST['join'])) { ?>
              <form name="form1" id="registerForm" method="post" action="" onsubmit="return checkSize();" enctype="multipart/form-data">
              <a href="<?php echo URL; ?>"><img src="<?php echo URL; ?>images/logo.png" class="mb-3" width="70"></a>
                <h2>Register</h2>
                <div class="form-group">
                  <select class="form-control" id="user_type" name="user_type">
                    <option value="0" selected>User</option>
                    <option value="1">Service Provider</option>
                  </select>
                  <small id="typeHelp" class="form-text text-muted">Select your account type.</small>
                </div>
                <div class="form-group">
                  <span id="sprytextfield3">
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email']; ?>" aria-describedby="emailHelp" placeholder="Email Address">
                    <span class="textfieldRequiredMsg">A value is required.</span>
                    <span class="textfieldInvalidFormatMsg">Invalid format.</span>
                  </span>
                  <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                </div>
                <div class="form-group">
                  <span id="sprytextfield1">
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $_POST['last_name']; ?>" aria-describedby="lastnameHelp" placeholder="Last Names">
                    <span class="textfieldRequiredMsg">A value is required.</span>
                  </span>
                  <small id="lastnameHelp" class="form-text text-muted">Please enter your full names as it appears on your ID.</small>
                </div>
                <div class="form-group" id="show_mobile_number" style="display: none;">
                  <input type="text" name="mobile_number" id="mobile_number" value="<?php echo $_POST['mobile_number']; ?>" required class="form-control" placeholder="Mobile Number">
                  <small id="mobileHelp" class="form-text text-muted">Please provide your phone number.</small>
                </div>
                <div class="form-group" id="show_street" style="display: none;">
                  <input type="text" name="street" id="street" value="<?php echo $_POST['street']; ?>" required class="form-control" placeholder="Address">
                  <small id="streetHelp" class="form-text text-muted">Please provide your address.</small>
                </div>
                <div class="form-group" id="show_city" style="display: none;">
                  <input type="text" name="city" id="city" value="<?php echo $_POST['city']; ?>" required class="form-control" placeholder="City">
                  <small id="cityHelp" class="form-text text-muted">Please provide your city.</small>
                </div>
                <div class="form-group" id="show_state" style="display: none;">
                  <input type="text" name="state" id="state" value="<?php echo $_POST['state']; ?>" required class="form-control" placeholder="State/Province">
                  <small id="stateHelp" class="form-text text-muted">Please provide your state or province.</small>
                </div>
                <div class="form-group" id="show_country" style="display: none;">
                  <select class="form-control" id="country" name="country">
                    <option value="Nigeria" selected>Nigeria</option>
                  </select>
                  <small id="countryHelp" class="form-text text-muted">Please select your country.</small>
                </div>
                <div class="form-group" id="show_photo_file" style="display: none;">
                  <input type="file" name="photo_file" id="photo_file" required class="form-control" placeholder="Upload Photo" accept="image/*">
                  <small id="photo_fileHelp" class="form-text text-muted">Please upload your photograph not more than 2MB.</small>
                </div>
                <div class="form-group" id="show_category_select" style="display: none;">
                  <select class="form-control" id="category_select" name="category_select[]" required multiple>
                    <?php for ($i = 0; $i < count($list); $i++) { ?>
                      <option value="<?php echo $list[$i]['ref']; ?>"><?php echo ucfirst(strtolower($list[$i]['category_title'])); ?></option>
                    <?php } ?>
                  </select>
                  <small id="countryHelp" class="form-text text-muted">Please select your Area(s) of expertise. You can select multiple option</small>
                </div>
                <div class="form-group" id="show_id_type" style="display: none;">
                  <select class="form-control" id="id_type" name="id_type" required>
                    <option value="">Select ID Type</option>
                    <?php for ($i = 0; $i < count($listID); $i++) { ?>
                      <option value="<?php echo $listID[$i]['ref']; ?>"><?php echo ucfirst(strtolower($listID[$i]['name'])); ?></option>
                    <?php } ?>
                  </select>
                  <small id="countryHelp" class="form-text text-muted">Please select your type of identification document</small>
                </div>
                <div class="form-group" id="show_id_expiry_mm" style="display: none;">
                  <div class="form-row">
                    <div class="col">
                      <input type="number" name="id_expiry_mm" id="id_expiry_mm" required class="form-control" placeholder="MM" min="01" max="12" value="<?php echo $_POST['id_expiry_mm']; ?>">
                    </div>
                    <div class="col">
                      <input type="number" name="id_expiry_yy" id="id_expiry_yy" required class="form-control" placeholder="YYYY" min="<?php echo date("Y"); ?>" max="<?php echo date("Y")+10; ?>" value="<?php echo $_POST['id_expiry_yy']; ?>">
                    </div>
                  </div>
                  <small id="id_expiryHelp" class="form-text text-muted">Please provide the expiry date MM-YYYY.</small>
                </div>
                <div class="form-group" id="show_id_number" style="display: none;">
                  <input type="text" name="id_number" id="id_number" required class="form-control" placeholder="ID Number" value="<?php echo $_POST['id_number']; ?>">
                  <small id="id_numberHelp" class="form-text text-muted">Please provide your the ID Number.</small>
                </div>
                <div class="form-group" id="show_id_file" style="display: none;">
                  <input type="file" name="id_file" id="id_file" required class="form-control" placeholder="Upload ID" accept="image/png, image/jpeg, Application/pdf" data-max-size="2048">
                  <small id="id_fileHelp" class="form-text text-muted">Please upload your ID not more than 2MB.</small>
                </div>
                <div class="form-group" id="show_kin_name" style="display: none;">
                  <input type="text" name="kin_name" id="kin_name" required class="form-control" placeholder="Next of Kin Name" value="<?php echo $_POST['kin_name']; ?>">
                  <small id="kin_nameHelp" class="form-text text-muted">Please provide your Next of Kin Name.</small>
                </div>
                <div class="form-group" id="show_kin_email" style="display: none;">
                  <input type="email" name="kin_email" id="kin_email" required class="form-control" placeholder="Next of Kin E-Mail" value="<?php echo $_POST['kin_email']; ?>">
                  <small id="kin_emailHelp" class="form-text text-muted">Please provide your Next of Kin E-Mail.</small>
                </div>
                <div class="form-group" id="show_kin_phone" style="display: none;">
                  <input type="number" name="kin_phone" id="kin_phone" required class="form-control" placeholder="Next of Kin Phone number" value="<?php echo $_POST['kin_phone']; ?>">
                  <small id="kin_phoneHelp" class="form-text text-muted">Please provide your Next of Kin Phone Number.</small>
                </div>
                <div class="form-group" id="show_kin_relationship" style="display: none;">
                  <input type="text" name="kin_relationship" id="kin_relationship" required class="form-control" placeholder="Next of Kin Relationship" value="<?php echo $_POST['kin_relationship']; ?>">
                  <small id="kin_relationshipHelp" class="form-text text-muted">Please provide your Next of Kin Relationship.</small>
                </div>
                <div class="form-group">
                  <span id="sprypassword1">
                    <input type="password" class="form-control" id="password2" name="password" aria-describedby="passwordHelp" placeholder="Password">
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
                  <span id="spryconfirm1">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" aria-describedby="confirmHelp" placeholder="Confirm Password">
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
                <button type="submit" name="register" id="register" disabled class="btn purple-bn">Register</button>
                <div class="form-group">
                  <div class="form-group col-md-2">
                    <div class="g-signin2" data-onsuccess="onSignIn"></div>
                  </div>
                </div>
                <div class="form-group">
                  <a href='login'>Login</a>
                </div>
              </form>
              <?php } else { ?>
              <form name="form1" method="post" action="">
              <a href="<?php echo URL; ?>"><img src="<?php echo URL; ?>images/logo.png" class="mb-3" width="70"></a>
                <h2>Login</h2>
                <div class="form-group">
                  <span id="sprytextfield5">
                      <input type="text" name="email" id="email" required class="form-control" placeholder="Email Address">
                      <span class="textfieldRequiredMsg">A value is required.</span>
                      <span class="textfieldInvalidFormatMsg">Invalid Email.</span>
                  </span>
                </div>
                <div class="form-group">
                  <span id="sprytextfield6">
                      <input type="password" name="password" id="password" required class="form-control" placeholder="Password">
                      <span class="textfieldRequiredMsg">A value is required.</span>
                  </span>
                </div>
                <button type="submit" name="login" id="login" class="btn purple-bn">Login</button>
                <div class="form-group">
                  <a href='<?php echo URL; ?>login?recoverPassword'>Forgot Password</a> | <a href='<?php echo URL; ?>login?join'>Register</a>
                </div>
                <div class="form-group">
                  <div class="g-signin2" data-onsuccess="onSignIn"></div>
                </div>
              </form>
              <?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>

	
    <!-- Bootstrap core JavaScript -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6/js/select2.min.js"></script>
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
var sprytextfield8 = new Spry.Widget.ValidationTextField("sprytextfield8");
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
$(document).ready(function() {
  $('#user_type').change(function() {
    if ($(this).val() == 1) {
      $('#show_mobile_number,#show_street,#show_city,#show_state,#show_country,#show_photo_file,#show_category_select,#show_id_type,#show_id_expiry_mm,#show_id_number,#show_id_file,#show_kin_name,#show_kin_email,#show_kin_phone,#show_kin_relationship').show();

      $('#category_select').select2({
        placeholder: "Select a skill(s) or Area(s) of expertise",
        allowClear: true
      });
      $('#mobile_number,#street,#city,#state,#country,#photo_file,#category_select,#id_type,#id_expiry_mm,#id_number,#id_file,#kin_name,#kin_email,#kin_phone,#kin_relationship').attr("required")      
    } else {
      $('#show_mobile_number,#show_street,#show_city,#show_state,#show_country,#show_photo_file,#show_category_select,#show_id_type,#show_id_expiry_mm,#show_id_number,#show_id_file,#show_kin_name,#show_kin_email,#show_kin_phone,#show_kin_relationship').hide();
      $('#mobile_number,#street,#city,#state,#country,#photo_file,#category_select,#id_type,#id_expiry_mm,#id_number,#id_file,#kin_name,#kin_email,#kin_phone,#kin_relationship').removeAttr("required")
    }
  })

  $('#photo_file, #id_file').change(function() {
    checkSize();
  });
  
  function checkSize() {
    //this.files[0].size gets the size of your file.

    var photo_file =  document.getElementById("photo_file").files[0].size;
    var id_file =  document.getElementById("id_file").files[0].size;

    if (photo_file > 2048) {
      alert("the profile picture is greater than the allowed size of 2MB");
      $('#photo_file').focus();
      return false;
    } else if (id_file > 2048) {
      alert("the photo ID picture is greater than the allowed size of 2MB");
      $('#id_file').focus();
      return false;
    } else {
      return true;
    }
  }

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
