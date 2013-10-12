 
<?php
include 'includes/settings.php';
include 'includes/database.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);

/**
 * Handle API actions
 *
 * add a beverage: ?action=add&beverage=beer&amount=0.5&token=asdf_1234
 */
if(isset($_GET['action']) && isset($_GET['token'])) {
  $action = $_GET['action'];
  $token = $_GET['token'];
  if($db->isValidToken($token)){
    $user = $db->getUserId($token);
    switch($action){
      case 'add':{
        if(isset($_GET['beverage']) && isset($_GET['amount']) && is_numeric($_GET['amount'])){
          $db->addBeverage($_GET['beverage'], $_GET['amount'], $user);
          header('HTTP/1.1 200 OK');
          echo '200';
          exit;
        }
        else{
          header('HTTP/1.1 400 Bad Request');
          echo '400';
          exit;
        }
      } break;
    }
  }
  else{
    header('HTTP/1.1 401 Unauthorized');
    echo '401';
    exit;
  }
}
// Display the page.
else{
  // Add beverages to get the stats for here.
  $beverages = array('beer');

  $overall_stats = array('');
  foreach ($beverages as $beverage) {
    $overall_stats[$beverage] = array(
      'count' => $db->getCount($beverage),
      'amount' => $db->getAmount($beverage),
      );
  }
?>
<html>
  <head>
    <title>1337.af Beer counter</title>
  </head>
  <body>
    <div class='count'>Beer count: <?php echo $overall_stats['beer']['count']; ?></div>
    <div class='count'>Beer amount: <?php echo $overall_stats['beer']['amount']; ?></div>
  </body>
</html>
<?php
}

?>