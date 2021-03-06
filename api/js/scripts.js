google.load("visualization", "1", { packages: ["corechart"] });
google.setOnLoadCallback(drawChart);

$('#date').on('change', function() {
	$("#submit").trigger('click');
});

function drawChart(arr)
{
	$(document).ready(function()
	{
		var date = $('#date').val();

		var temperature = {
			json: 'temperature',
			idChart: 'chart-temp',
			title: 'Temperatura (°C)'
		};

		var humidity = {
			json: 'humidity',
			idChart: 'chart-humidity',
			title: 'Humidade (%)'
		};

		var dew_point = {
			json: 'dew_point',
			idChart: 'chart-dew-point',
			title: 'Ponto de orvalho (°C)   [13-16° Confortável, 10-12° Muito Confortável, <10° Pouco seco para alguns]'
		};

		getData(date, temperature);
		getData(date, humidity);
		getData(date, dew_point);
	});

	function getData(date, options)
	{
		$.ajax({
			data: { 
				date: date,
				json: options.json
			},
			dateType: 'json'
		}).success(function(result){

			console.log('in: ', result.in);
			console.log('out: ', result.out);

			var minmax = result.data[0]
			
			var data  = google.visualization.arrayToDataTable(result.data);
			var chart = new google.visualization.LineChart(document.getElementById(options.idChart));

			chart.draw(data, { title: options.title });

		}).fail(function(error) {
			console.error(error);
		})
	}
}