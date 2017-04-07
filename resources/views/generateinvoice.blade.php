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
                            <div id="progressLoading" class="col-sm-6 col-sm-offset-3 text-center" style="display:none">
                                <h3>Loading</h3>
                                <div class="progress progress-sm active">
                                    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    </div>
                                </div>
                            </div>

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
    $('#formGenerate').submit(function(e){
        e.preventDefault();
        var data = $(this).serialize();
        var progressBar = $('.progress-bar').first();
        $('#progressLoading').show();
        $.ajax({
            xhr: function() {
                var xhr = $.ajaxSettings.xhr();
                if(xhr.upload){
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var max = evt.total;
                            var current = evt.loaded;
                            var Percentage = (current * 100)/max;
                            console.log(Percentage, 'upload');
                            //Do something with upload progress here
                            progressBar.width(Math.round(Percentage) + '%');
                            if(Percentage >= 100) {
                                $('#progressLoading').fadeOut(500);
                            }
                        }
                   }, false);
                }

               xhr.addEventListener("progress", function(evt) {
                   if (evt.lengthComputable) {
                        var max = evt.total;
                        var current = evt.loaded;
                        var Percentage = (current * 100)/max;
                        console.log(Percentage, 'download');
                        //Do something with download progress
                        progressBar.width(Math.round(Percentage) + '%');
                        if(Percentage >= 100) {
                            $('#progressLoading').fadeOut(500);
                        }
                   }
               }, false);

               return xhr;
            },
            // cache:false,
            // contentType: false,
            // processData: false,
            type: 'POST',
            url: '{{route('invoice.generate')}}',
            data: data,
            success: function(result){
                if(result.errorMsg){ 
                    $.messager.alert('Warning',result.errorMsg); 
                    $('#generateResult').html('');
                }
                else{ $('#generateResult').html(result); }
            }
        });
    });
</script>
@endsection
