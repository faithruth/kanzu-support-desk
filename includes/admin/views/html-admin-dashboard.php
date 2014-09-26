<h2 class="admin-ksd-tab-title"><?php __('Dashboard','kanzu-support-desk'); ?></h2>
<p><?php _e('Welcome to Kanzu Support Desk','kanzu-support-desk'); ?>
</p>


<table width="100%">
<tr>
 <td colspan="5"> Ticket Stats</td>
</tr>
<tr>
    <td>New: 1007<td> <td>Closed: 300<td> <td>Open: 1000<td> <td>Avg reply time: 1 hr<td> <td>Avg service rating: 7/10<td>
<tr>
</table>



<table width="100%">

<tr>
  <tr>
    <td colspan="2">
		<div style="width:100%">
			<div>
				<canvas id="canvas-line" height="100%" width=""></canvas>
			</div>
		</div>
	<script>
		var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
		var lineChartData = {
			labels : ["January","February","March","April","May","June","July"],
			datasets : [
				{
					label: "Open tickets",
					fillColor : "rgba(220,220,220,0.2)",
					strokeColor : "rgba(220,220,220,1)",
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
					data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
				},
				{
					label: "Closed Tickets",
					fillColor : "rgba(151,187,205,0.2)",
					strokeColor : "rgba(151,187,205,1)",
					pointColor : "rgba(151,187,205,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(151,187,205,1)",
					data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
				}
			]

		}

	window.onload = function(){
		var ctx = document.getElementById("canvas-line").getContext("2d");
		window.myLine = new Chart(ctx).Line(lineChartData, {
			responsive: true
		});
	}
	</script>

    </td>
  </tr>
</tr>

<tr>
<td>



		<div id="canvas-holder">
			<canvas id="chart-area" width="300" height="300"/>
		</div>
	<script>

		var pieData = [
				{
					value: 300,
					color:"#F7464A",
					highlight: "#FF5A5E",
					label: "Open"
				},
				{
					value: 50,
					color: "#46BFBD",
					highlight: "#5AD3D1",
					label: "Closed"
				},
				{
					value: 100,
					color: "#FDB45C",
					highlight: "#FFC870",
					label: "Assigned"
				}

			];

			//window.onload = function(){
			//	var ctx = document.getElementById("chart-area").getContext("2d");
			//	window.myPie = new Chart(ctx).Pie(pieData);
			//};

	</script>

</td>

<tr>

</td>
</tr>
</table>	
	
