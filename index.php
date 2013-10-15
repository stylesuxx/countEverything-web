<?php
include 'includes/settings.php';
include 'includes/database.php';
$db = new Database($db_host, $db_name, $db_user, $db_pass);

$rows = '';
$items = $db->getAllStats();
foreach ($items as $item) {
  $rows .= '<tr><td>' . $item['name'] . '</td><td>' . $item['count'] . '</td><td>' . $item['amount'] . '</td></tr>';
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Counter</title>
  </head>
  <body>
    <table>
      <thead><th>Name</th><th>Count</th><th>Amount</th></thead>
      <tbody>
        <?php echo $rows; ?>
      </tbody>
    </table>
  </body>
</html>