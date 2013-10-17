<!DOCTYPE html>
<html>
  <head>
    <title>Counter</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script src="http://code.highcharts.com/highcharts.js"></script>
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
            
            series: <?php echo $content; ?>
        });
    });
    </script>
  </body>
</html>