@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Backup / Restore
@endsection

<!-- page title -->
@section('contentheader_title')
   Backup / Restore Data
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
        <li class="active">Backup / Restore</li>
    </ol>
@stop

@section('main-content')
<div class="container spark-screen">

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

		<div class="row">	
      <div class="col-sm-12 col-md-6">
        <div class="box box-solid">
            <div class="box-body">
                <h4 style="background-color:#f7f7f7; font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;">
                    DOWNLOAD DB BACKUP
                </h4>
                <div class="media">
                    
                    <div class="media-body">
                        <div class="clearfix">
                            <form action="{{route('backup.download')}}" method="post">
                            <center><button class="btn btn-block btn-warning btn-lg">DOWNLOAD</button></center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="row">
    <div class="col-sm-12 col-md-6">
        <div class="box box-solid">
            <div class="box-body">
                <h4 style="background-color:#f7f7f7; font-size: 18px; text-align: center; padding: 7px 10px; margin-top: 0;">
                    RESTORE DB BACKUP
                </h4>
                <div class="media">
                    
                    <div class="media-body">
                        <div class="clearfix">
                            <form action="{{route('backup.restore')}}" method="post" enctype="multipart/form-data">
                            <center>
                              <input type="file" name="dbfile" id="upload" class="form-control" accept=".sql"><br>
                              <button class="btn btn-block btn-success btn-lg">UPLOAD</button>
                            </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

		</div>
	</div>
@endsection