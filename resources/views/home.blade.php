@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection

@section('contentheader_title')
Dashboard
@endsection


@section('main-content')
	<div class="row">
		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-aqua">
				<div class="inner">
					<h3 style="font-size: 28px;">Rp. {{number_format($out[0]->ttl,2)}}</h3>

					<p>Outstanding Invoice</p>
				</div>
				<div class="icon">
					<i class="ion ion-cash"></i>
				</div>
				<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
			</div>
		</div>
		<!-- ./col -->
		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3 style="font-size: 28px;">{{ $inv }}</h3>

					<p>Invoice This Month</p>
				</div>
				<div class="icon">
					<i class="ion ion-document"></i>
				</div>
				<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
			</div>
		</div>
		<!-- ./col -->
		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3 style="font-size: 28px;">{{ $tenant }}</h3>

					<p>Total Tenant</p>
				</div>
				<div class="icon">
					<i class="ion ion-person-stalker"></i>
				</div>
				<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
			</div>
		</div>
		<!-- ./col -->
		<div class="col-lg-3 col-xs-6">
		<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
		  			<h3 style="font-size: 28px;">{{ $unit }}</h3>

		  			<p>Total Unit</p>
				</div>
				<div class="icon">
			 		<i class="ion ion-ios-home"></i>
				</div>
				<a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
			</div>
		</div>
		<!-- ./col -->
	</div>
	<div class="row">
		<div class="col-md-6">
			<!-- BAR CHART -->
			<div class="box box-success">
				<div class="box-header with-border">
				  <h3 class="box-title">Outstanding and Payment <?php echo date('Y'); ?></h3>

				  <div class="box-tools pull-right">
				    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
				    </button>
				    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				  </div>
				</div>
				<div class="box-body">
				  <div class="chart">
				    <canvas id="barChart" style="height:290px"></canvas>
				  </div>
				</div>
				<!-- /.box-body -->
				<div class="box-footer no-border">
					<div class="row">
            <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4">
            	<div style="background-color:rgba(210, 214, 222, 1); width: 50px; margin-left: auto; margin-right: auto;">&nbsp;</div>
              <div class="knob-label">Outstanding</div>
            </div>
            <!-- ./col -->
            <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4">
            	<div style="background-color:#229426; width: 50px; margin-left: auto; margin-right: auto;">&nbsp;</div>
              <div class="knob-label">Payment</div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->
				</div>
			</div>
			<!-- /.box -->

        </div>
        <div class="col-md-6">
			<!-- DONUT CHART -->
			<div class="box box-danger">
				<div class="box-header with-border">
				  <h3 class="box-title">Outstanding vs Payment  <?php echo date('Y'); ?></h3>

				  <div class="box-tools pull-right">
				    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
				    </button>
				    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				  </div>
				</div>
				<div class="box-body">
				  <canvas id="pieChart" style="height:250px"></canvas>
				</div>
				<!-- /.box-body -->
        <div class="box-footer no-border">
          <div class="row">
            <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4">
              <label>Outstanding</label>
              <div style="background-color:#f56954; width: 50px; margin-left: auto; margin-right: auto;">&nbsp;</div>
              <div class="knob-label">{{ number_format($hutang_vs,2) }}</div>
            </div>
            <!-- ./col -->
            <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4">
              <label>Payment</label>
              <div style="background-color:#00a65a; width: 50px; margin-left: auto; margin-right: auto;">&nbsp;</div>
              <div class="knob-label">{{ number_format($bayar_vs,2) }}</div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->
        </div>
			</div>
			<!-- /.box -->
        </div>
	</div>
@endsection
@section('footer-scripts')
<script type="text/javascript" src="{{ asset('plugins/chartjs/Chart.min.js') }}"></script>
<script>
  $(function () {

  	var areaChartData = {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Des"],
      datasets: [
        {
          label: "Outstanding",
          fillColor: "rgba(210, 214, 222, 1)",
          strokeColor: "rgba(210, 214, 222, 1)",
          pointColor: "rgba(210, 214, 222, 1)",
          pointStrokeColor: "#c1c7d1",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(220,220,220,1)",
          data: {{ $hutang }}
        },
        {
          label: "Payment",
          fillColor: "rgba(60,141,188,0.9)",
          strokeColor: "rgba(60,141,188,0.8)",
          pointColor: "#3b8bba",
          pointStrokeColor: "rgba(60,141,188,1)",
          pointHighlightFill: "#fff",
          pointHighlightStroke: "rgba(60,141,188,1)",
          data: {{ $bayar }}
        }
      ]
    };
    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas = $("#barChart").get(0).getContext("2d");
    var barChart = new Chart(barChartCanvas);
    var barChartData = areaChartData;
    barChartData.datasets[1].fillColor = "#00a65a";
    barChartData.datasets[1].strokeColor = "#00a65a";
    barChartData.datasets[1].pointColor = "#00a65a";
    var barChartOptions = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero: true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines: true,
      //String - Colour of the grid lines
      scaleGridLineColor: "rgba(0,0,0,.05)",
      //Number - Width of the grid lines
      scaleGridLineWidth: 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines: true,
      //Boolean - If there is a stroke on each bar
      barShowStroke: true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth: 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing: 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing: 1,
      //String - A legend template
      legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
      //Boolean - whether to make the chart responsive
      responsive: true,
      maintainAspectRatio: true
    };

    barChartOptions.datasetFill = false;
    barChart.Bar(barChartData, barChartOptions);

    //-------------
    //- PIE CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
    var pieChartCanvas = $("#pieChart").get(0).getContext("2d");
    var pieChart = new Chart(pieChartCanvas);
    var PieData = [
      {
        value: {{ $hutang_persen }},
        color: "#f56954",
        highlight: "#f56954",
        label: "Outstanding (%) "
      },
      {
        value: {{ $bayar_persen }},
        color: "#00a65a",
        highlight: "#00a65a",
        label: "Payment (%) "
      }
    ];
    var pieOptions = {
      //Boolean - Whether we should show a stroke on each segment
      segmentShowStroke: true,
      //String - The colour of each segment stroke
      segmentStrokeColor: "#fff",
      //Number - The width of each segment stroke
      segmentStrokeWidth: 2,
      //Number - The percentage of the chart that we cut out of the middle
      percentageInnerCutout: 50, // This is 0 for Pie charts
      //Number - Amount of animation steps
      animationSteps: 100,
      //String - Animation easing effect
      animationEasing: "easeOutBounce",
      //Boolean - Whether we animate the rotation of the Doughnut
      animateRotate: true,
      //Boolean - Whether we animate scaling the Doughnut from the centre
      animateScale: false,
      //Boolean - whether to make the chart responsive to window resizing
      responsive: true,
      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio: true,
      //String - A legend template
      legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>"
    };
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    pieChart.Doughnut(PieData, pieOptions);

  });
</script>
@endsection
