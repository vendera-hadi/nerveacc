@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Generate Invoice
@endsection

<!-- page title -->
@section('contentheader_title')
   Generate Invoice
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Generate Invoice</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->
                <div class="box" style="min-height:500px">
                    <div class="box-body">
                        <h4>Invoice Filter</h4>
                        <form action="post" id="formGenerate">
                        <div class ="row">
                            <div class="col-sm-3">
                                <select id="month" name="month" class="form-control" required>
                                    <option value="">Choose Month</option>
                                    @for($i=1;$i<=12;$i++)
                                    <?php $time = DateTime::createFromFormat('!m', $i); ?>
                                    <option value="{{$i}}">{{$time->format('F')}}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select id="year" name="year" class="form-control" required>
                                    <option value="">Choose Year</option>
                                    @for($i=2016;$i<=date('Y');$i++)
                                    <option value="{{$i}}">{{$i}}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <button class="btn btn-info">Submit</button>
                            </div>
                        </div>
                        </form>

                        <div class ="row" style="margin-top:80px">
                            <div class="col-sm-12 text-center" id="generateResult">
                            </div>
                        </div>
                    </div>
                </div>

        	</div>
		</div>
	</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
    // var interval = intervalTrigger();
    // function intervalTrigger() {
    //   return window.setInterval( function() {
    //             $.post('{{url('progressgenerate')}}', $('#formGenerate').serialize(), function(result){
    //                 console.log(result);
    //             });
    //           }, 1000 );
    // };

    $('#formGenerate').submit(function(e){
        e.preventDefault();
        var data = $(this).serialize();
        $('#generateResult').html('<img src="{{asset('img/facebook.gif')}}">');
        $.post('{{route('invoice.generate')}}',data, function(result){
            if(result.errorMsg){ 
                $.messager.alert('Warning',result.errorMsg); 
                $('#generateResult').html('');
            }
            else{ $('#generateResult').html(result); }
        }).fail(function() {
            $('#generateResult').html("<h3>Process Done in Background</h3>");
        });
    });
</script>
@endsection
