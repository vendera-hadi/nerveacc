@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Other Config
@endsection

<!-- page title -->
@section('contentheader_title')
    Other Config
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Other Config</li>
    </ol>
@stop

@section('main-content')
<div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">
                <!-- content -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                  <h3 class="box-title">Other Config Form</h3>
                                </div>
                                <!-- /.box-header -->
                                <!-- form start -->
                                
                                <form action="{{route('config.update')}}" method="post" enctype="multipart/form-data">
                                  <div class="box-body">
                                    <div class="col-sm-12">

                                        @if(Session::get('error'))
                                            <div class="alert alert-danger">
                                              <strong>Error!</strong> {{ Session::get('error') }}
                                            </div>
                                        @endif

                                        @if(Session::get('success'))
                                            <div class="alert alert-success">
                                              <strong>Success</strong> {{ Session::get('success') }}
                                            </div>
                                        @endif

                                        @if (count($errors) > 0)
                                          <div class="alert alert-danger">
                                            <ul>
                                              @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                              @endforeach
                                            </ul>
                                          </div>
                                        @endif
                                    
                                        <div class="form-group">
                                          <label>Footer Invoice Text</label>
                                          <textarea class="textarea" name="footer_invoice" class="form-control" style="width: 100%;" required>{{$footer}}</textarea>
                                        </div>

                                        <div class="form-group">
                                          <label>Footer Invoice Label</label>
                                          <textarea class="textarea" name="footer_label_inv" class='form-control' style="width: 100%;" required>{{$label}}</textarea>
                                        </div>

                                                                            
                                    </div>
                                  </div>
                                  <!-- /.box-body -->

                                  <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                  </div>
                                </form>
                                

                              </div>
                        </div>
                    </div>
                <!-- content -->
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
<script type="text/javascript" src="{{asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script type="text/javascript">
$(document).ready(function() {
    $(".numeric").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $(".textarea").wysihtml5();
});
</script>
@endsection