 
<?php
include 'includes/settings.php';
include 'includes/database.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);

if(isset($_GET['action'])) {
  $action = $_GET['action'];
  switch($action) {
    case 'add': {
      // If a beverage is set and the token matches, write to db.
      if(isset($_GET['beverage']) && isset($_GET['token']) && $_GET['token'] == $api_token){
        $db->write($_GET['beverage']);
        header("HTTP/1.1 200 OK");
        exit;
      }
      else{
        header("HTTP/1.1 400 Bad Request");
        exit;
      }
    } break;
  }
}
else{
  // Add beverages to get the stats for here.
  $beverages = array('beer');

  $overall_stats = array('');
  foreach ($beverages as $beverage) {
    $overall_stats[$beverage] = array(
      'count' => $db->getCount($beverage),
      );
  }
?>
<html>
  <head>
    <title>1337.af Beer counter</title>
  </head>
  <body>
    <div class='count'>Beer count: <?php echo $overall_stats['beer']['count']; ?></div>
  </body>
</html>
<?php
}

?>