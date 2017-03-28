<!DOCTYPE html>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <head>
    <title>Cetak Report</title>
    <style>
    <?php include(public_path().'/css/bootstrap.css');?>
    </style>
    <link href="{{ asset('/css/AdminLTE.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/skins/skin-blue.css') }}" rel="stylesheet" type="text/css" />

    
  </head>
    <body>
    <!-- Main content -->
    <section class="invoice">
    <div class="box-body table-responsive">
      <table class="" width="100%" style="margin-bottom: 10px;">
        <tr class="page-header">
          <td width="50%">
            <h2>
              {{$name}}
            </h2>
          </td>
          <td width="50%">
            <img src="@if(!empty($logo)){{asset($logo)}}@endif" width="120" class="pull-right">
          </td>
        </tr>
        <tr>
          <td width="50%">
            <h4>{{$title}}</h4>
            <h4>{{$tahun}}</h4>
          </td>
        </tr>
      </table>
      @include($template)
    </div>
      </section>

  </body>
  </html>