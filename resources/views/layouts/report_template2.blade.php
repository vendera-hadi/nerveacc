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
            <h3>
              {{$name}}
            </h3>
          </td>
          <td width="50%">
            <img src="@if(!empty($logo)){{asset('upload/'.$logo)}}@endif" width="150" class="pull-right">
          </td>
        </tr>
        <tr>
          <td width="50%">
            <h5>{{$title}}</h5>
            <h5>{!! $tahun !!}</h5>
            
          </td>
        </tr>
      </table>
      <div style="font-size: 9pt !important;">
      @include($template)
      </div>
    </div>
      </section>

  </body>
<?php if($type == 'print'){ ?>
    <script type="text/javascript">
        window.print();
    </script>
<?php } ?>
  </html>