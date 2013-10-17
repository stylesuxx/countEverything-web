<?php
include 'includes/settings.php';
include 'includes/database.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);

$rows = '';
$items = $db->getAllItemStats();
foreach ($items as $item) {
  $rows .= '<tr><th>' . $item['name'] . '</th><td>' . $item['amount'] . '</td></tr>';
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Counter</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="http://code.highcharts.com/modules/data.js"></script>
    <script src="http://code.highcharts.com/modules/exporting.js"></script>
    <script src="js/script.js"></script>
  </head>
  <body>
    <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    <table id="data">
      <thead><th></th><th>Amount</th></thead>
      <tbody>
        <?php echo $rows; ?>
      </tbody>
    </table>
  </body>
</html>
