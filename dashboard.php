<?php
// Initialize the session
session_start();
//Regenerate Session Id
session_regenerate_id();
//Report All Errors
error_reporting(0);
// If session variable is not set it will redirect to login page
if(!isset($_SESSION['recieve_address']) || empty($_SESSION['recieve_address'])){
  header("location: index.php");
  exit;
}else {
          $now = time(); // Checking the time now when home page starts.
        //Checking if session ended
        if ($now > $_SESSION['expire']) {
            session_destroy();
            header("location: index.php");      
        }
}

$recieve_address = $_SESSION["recieve_address"];

//Require files
require_once("settings/configs.php");
require_once("scripts/process.php");
require_once("libs/solvemedia.php");
require_once("libs/kswallet.php");
require_once("database/db.php");

//Start Kswallet
$KW = new Kswallet();

//Check if website is under maintenance
WebsiteMaintenance('1');

//Get the User IP Address
$userip = getUserIP();
//Get the user Referal
$user_ref = GetUserReferal($recieve_address);

//Errors
$captcha_err = "";

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

    //Get Current time
    $time = getTime();
    //Check if user can claim
    $check_claim_status = CheckIfUserCanClaim($recieve_address);
    if ($check_claim_status == "ok") {

    //Get total amount of coins to send + mystery bonus
    $RandomBonusPercentage = RandomNumberRoll(0, 100);
    $totalreward = ($RandomBonusPercentage / 100) * $claim_reward;
    $totalreward = $claim_reward + $totalreward;
    $totalreward = round($totalreward);
    echo $totalreward;
    echo "<br>";
    echo "You got $RandomBonusPercentage % Mystery Bonus";

    //Get percentage from the amount and send to ref of this user
    $ref_percentage = ($referal_percentage / 100) * $claim_reward;
    $ref_percentage = round($ref_percentage);
    $pay_referal = $KW->pay_user($user_ref,$ref_percentage,$currency);
    echo "Referal Got $ref_percentage referal commission";

    //Update user claims,balance,last claim
    $update_user_info = UpdateUserFInfo($recieve_address,$totalreward,$time);

    //Update referal balance and referal earnings
    $update_user_referal = UpdateUserReferalEarnings($user_ref,$ref_percentage);

    //Send coins to Kswallet balance of this recieve_address
    $pay_user = $KW->pay_user($recieve_address,$totalreward,$currency);

      }else{
    //If user can not claim throw the wait time
    $check_claim_status = gmdate("H:i:s", $check_claim_status);
    $need = "<div class='ui violet icon message'>
    <i class='notched circle loading icon'></i>
    <div class='header'>
    <div class='header'>
      Please Wait
    </div>
    <p>You need to wait <b>$check_claim_status</b> minutes.</p>
    </div>
    </div>";
    echo $need;
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
<h1>Welcome <strong><?php echo $recieve_address; ?></strong>!</h1>
<?php
echo $captcha_err;
?>
<input id="ClaimNow" type="button" name="claim" value="Claim Now" />
<script type="text/javascript">
  $('#ClaimNow').click(function() {
  $('#faucetclaim').toggle('slow', function() {
    // Animation complete.
  });
});
</script>
<br>
<br>
<br>
<!-- Faucet DIV -->
<div id="faucetclaim"  style="display:none;">
<form method="POST" action="">
  <div class="field">
<?php echo solvemedia_get_html($public_key); ?>
  </div>
<button class="ui blue button" type="submit" style="width: 305px;height: auto;background:#4caf50;">Get Your Coins</button>
</form>
</div>
  </div>
  <!-- Row 3 -->
  <div class="column">
</div>
  </div>
</div>  
</center>  
</body>