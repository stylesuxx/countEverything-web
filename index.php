<?php
include 'includes/settings.php';
include 'includes/database.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);

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