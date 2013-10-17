<?php
include 'includes/settings.php';
include 'includes/database.php';
include 'includes/api.php';

$db = new Database($db_host, $db_name, $db_user, $db_pass);

$items = $db->getAllItems(7);
$json = array();
foreach ($items as $key => $value) {
  $line = array('name' => $key, 'data' => array());
  $rows = $items[$key];
  foreach ($rows as $row) {
    $timestamp = strtotime($row['date']);
    $line['data'][] = array($timestamp*1000, (float)$row['amount']);
  }
  $json[] = $line;
}

$json = json_encode($json);
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Counter</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="http://code.highcharts.com/modules/data.js"></script>
    <script src="http://code.highcharts.com/modules/exporting.js"></script>
  </head>
  <body>
    <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    <script>
    $(function () {
        $('#container').highcharts({
            chart: {
                type: 'spline'
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                type: 'datetime',
                dateTimeLabelFormats: { // don't display the dummy year
                    month: '%e. %b',
                    year: '%b'
                },
                tickInterval: 24 * 3600 * 1000
            },
            yAxis: {
                title: {
                    text: 'Amount'
                },
                min: 0
            },
            tooltip: {
                formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y;
                }
            },
            
            series: <?php echo $json; ?>
        });
    });
    </script>
  </body>
</html>
