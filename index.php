<?php

//Report all errors
error_reporting(E_ALL);

//Require files
require_once("settings/configs.php");
require_once("scripts/process.php");
require_once("libs/solvemedia.php");
require_once("libs/kswallet.php");
require_once("database/db.php");


//Start Session
session_start();

//Check if user is logged in
if (isset($_SESSION['recieve_address'])) {
    header("location: dashboard.php");
    exit;
}

//Start Kswallet
$KW = new Kswallet();

//Check if website is under maintenance
WebsiteMaintenance('1');

//Get the User IP Address
$ip = getUserIP();
//Create random id for Pub ID
$pubbid = RandomId(18);
// Define variables and initialize with empty values
$captcha_err = $recieve_address_err_2 = $recieve_address_err = "";
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $solvemedia_response = solvemedia_check_answer(
        $privkey,
        $_SERVER["REMOTE_ADDR"],
        $_POST["adcopy_challenge"],
        $_POST["adcopy_response"],
        $hashkey
    );
    if (!$solvemedia_response->is_valid) {
        $captcha_err = '<div class="ui red message">Failed to verify captcha!</div>';
    } else {

    //Check if recieve_address is empty
    if (empty($_POST['recieve_address'])) {
      $recieve_address_err = '<div class="ui red message">Please enter your Recieve Address.</div>';
    }elseif ($KW->check_address(htmlspecialchars(stripslashes($_POST['recieve_address']))) == 1110) {
      $recieve_address_err_2 = '<div class="ui red message">Invalid Recieve Address, Get your Recieve Address now - <a target="_BLANK" href="https://www.kswallet.net/register">Register to Kswallet</a>.</div>';
    } else {
      $recieve_address = htmlspecialchars(stripslashes($_POST['recieve_address']));
    }
    
    //Check if error free
    if (empty($captcha_err) && empty($recieve_address_err) && empty($recieve_address_err_2)) {

    //Check if recieve_address is already in this faucet database
    $check_recieve_address = CheckRecieveAddress($recieve_address);

    //Validate login process
    if ($check_recieve_address == "ok") {
     session_start();
     session_regenerate_id(true);
     $_SESSION['start'] = time();
     $_SESSION['expire'] = $_SESSION['start'] + (60 * 60);
     $_SESSION['recieve_address'] = $recieve_address;
     header("location: dashboard.php");
    }else{
    //Add recieve address into database
     $insert_recieve_address = InsertRecieveAddress($recieve_address,$ip);
     if ($insert_recieve_address == "ok") {
     session_start();
     session_regenerate_id(true);
     $_SESSION['start'] = time();
     $_SESSION['expire'] = $_SESSION['start'] + (60 * 60);
     $_SESSION['recieve_address'] = $recieve_address;
     header("location: dashboard.php");
     }else{
      echo "Database Error";
     }
      }
    }
  }
}

?>
<head>
  <!-- Website informations -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $web_title; ?> Log In</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo $web_description; ?>" />
  <meta name="keywords" content="<?php echo $web_keywords; ?>" />
  <meta name="author" content="Kswallet Team" />
  <link rel="shortcut icon" type="image/png" href="<?php $url; ?>images/favicon1.png"/>

  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?php echo $web_title; ?> Log In" />
  <meta property="og:url" content="<?php echo $url; ?>" />
  <meta name="og:description" content="Log in your Kswallet Account." />
  <meta property="og:image" content="<?php echo $url; ?>/images/meta/1.jpg" />
  <meta property="og:image:secure_url" content="<?php echo $url; ?>/images/meta/1.jpg" />
  <meta property="og:image:type" content="image/png" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />

    <!-- Semantic -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.3/semantic.min.css">
    <script
    src="https://code.jquery.com/jquery-3.1.1.min.js"
    integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
    crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.3/semantic.min.js"></script>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script> 
</head>
<!-- login register -->
<br>
<br>
<br>
<body style="background-image: url('images/background_5.jpg');background-size:cover;border-top:4px solid #2185d0;">
<center>
<!-- Container with 3 rows -->
<div class="ui three column doubling stackable grid container">
  <!-- Row 1 -->
  <div class="column">
  </div>  
  <!-- Row 2 -->
  <div class="column">
<br>
<img height="auto" width="auto" src="images/logo.png">
<br>
<br>
  <div class="ui horizontal divider" style="margin-bottom: -25px;">
    <p style="color: #333;">Log In</p>
  </div>
<br>
<?php
echo $captcha_err;
echo $recieve_address_err_2;
echo $recieve_address_err;
?>  
<br>    
<form action="" method="POST" class="ui inverted form" autocomplete="off" style="background:#fff;padding: 15px;border-radius: 3px;border:1px solid #d9d9d9;box-shadow: 0 1px 2px rgba(0, 0, 0, .07);">
  <div class="field">
    <label style="color:#333;text-align: left;letter-spacing: 1px;"><img src="images/assets/login-username.png">Recieve Address</label>
    <input type="text" name="recieve_address" placeholder="Recieve Address" style="background:#fff;border:1px solid #ddd;">
  </div>
  <div class="field">
<?php echo solvemedia_get_html($public_key); ?>
  </div>
  <button class="ui blue button" type="submit" style="width: 305px;height: auto;background:#4caf50;">Log In</button>
</form>
  </div>
  <!-- Row 3 -->
  <div class="column">
</div>
  </div>
</div>  
</center>  
</body>