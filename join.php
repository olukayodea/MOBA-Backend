<?php
  include_once("includes/functions.php");

    $urlParam = $common->getParam($_SERVER['REQUEST_URI']);
    $tagLink = $redirect."?".$urlParam;

    if (isset($_REQUEST['social_media'])) {
        $data = base64_decode($_REQUEST['data']);
        $cleanData = explode("+", $data);
        $name = explode(" ", $cleanData[0]);
        $last_name = $name[0];
        $other_names = $name[1];
        $email = $cleanData[2];
        $image_url = $cleanData[1];
        $login_type = "social_media";
    } else {
        header("location: ".URL);
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
<?php $pageHeader->loginStrip(true); ?>
<?php $pageHeader->navigation(); ?>
<div align="center">
<form name="form1" method="post" action="<?php echo URL."login"; ?>">
  <table width="50%" border="0">
    <tr align="center">
      <td colspan="2"><strong>Register</strong></td>
      </tr>
    <tr align="center">
      <td colspan="2">Welcome <?php echo $other_names; ?>, to complete your signup, please setup your password and your screen name. This password is only used on MOBA.com site and not anywhere else</td>
      </tr>
    <tr>
      <td align="right">Screen Name</td>
      <td><span id="sprytextfield4">
        <input type="text" name="screen_name" id="screen_name" required>
        <span class="textfieldRequiredMsg">A value is required.</span></span></td>
    </tr>
    <tr>
      <td align="right">Password</td>
      <td><span id="sprypassword1">
      <input type="password" name="password" id="password2" required>
      <span class="passwordRequiredMsg">A value is required.</span><span class="passwordMinCharsMsg">Minimum number of characters not met.</span><span class="passwordInvalidStrengthMsg">The password doesn't meet the specified strength.</span></span></td>
    </tr>
    <tr>
      <td align="right">Confirm Password</td>
      <td><span id="spryconfirm1">
        <input type="password" name="confirm_password" id="confirm_password" required>
<span class="confirmInvalidMsg">The values don't match.</span></span></td>
    </tr>
    <tr>
      <td align="right">&nbsp;</td>
      <td>
          <input type="submit" name="register" id="register" value="Register" disabled>
          <input type="hidden" name="email" value="<?php echo $email; ?>">
          <input type="hidden" name="other_names" value="<?php echo $other_names; ?>">
          <input type="hidden" name="last_name" value="<?php echo $last_name; ?>">
          <input type="hidden" name="image_url" value="<?php echo $image_url; ?>">
          <input type="hidden" name="login_type" value="<?php echo $login_type; ?>">
        </td>
    </tr>
    <tr>
      <td align="right">&nbsp;</td>
      <td><div id="pswd_info">
    Password requirements:
    <ul>
        <li id="letter" class="invalid">At least <strong>one letter</strong></li>
        <li id="capital" class="invalid">At least <strong>one capital letter</strong></li>
        <li id="number" class="invalid">At least <strong>one number</strong></li>
        <li id="length" class="invalid">Be at least <strong>6 characters</strong></li>
    </ul>
</div></td>
    </tr>
    <tr align="right">
      <td colspan="2" align="center"><a href='<?php echo URL; ?>login'>Login</a></td>
      </tr>
  </table>
</form>
</div>
<?php $pageHeader->jsFooter(); ?>
<script src="https://apis.google.com/js/platform.js" async defer></script>
<script type="text/javascript">
  var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4");
  var sprypassword1 = new Spry.Widget.ValidationPassword("sprypassword1", {minChars:6, minAlphaChars:1, minNumbers:1, minUpperAlphaChars:1, validateOn:["change"]});
  var spryconfirm1 = new Spry.Widget.ValidationConfirm("spryconfirm1", "password2", {isRequired:false});
    
  $(document).ready(function() {
    $('#pswd_info').css('display', 'none');
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
        $('input[type=submit]').prop( "disabled", false );
      } else {
        $('input[type=submit]').prop( "disabled", true );
      }

    }).focus(function() {
      $('#pswd_info').show();
    }).blur(function() {
      $('#pswd_info').hide();
    });
  });
</script>
</body>
</html>