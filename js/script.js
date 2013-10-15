$(function () {
  $('#container').highcharts({
    data: {
      table: document.getElementById('data')
    },
    chart: {
      type: 'column'
    },
    title: {
      text: 'Overall item amount'
    },
    yAxis: {
      allowDecimals: true,
      title: {
        text: 'Units'
      }
    },
    tooltip: {
      formatter: function() {
        return '<b>'+ this.series.name +'</b><br/>'+
          this.y +' '+ this.x.toLowerCase();
      }
    }
  });
});