<!DOCTYPE html>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <head>
    <title>Cetak Faktur</title>
    <link href="{{ asset('/css/AdminLTE.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/skins/skin-blue.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
      .pull-right{
        float: right;
      }
      .table {
          border-collapse: collapse;
      }
      .table, .table th, .table td {
          border: 1px solid black !important;
      }
    </style>
  </head>
    <body>

  
    <!-- Main content -->
    <section class="invoice">
      <table class="" width="100%" style="margin-bottom: 50px;">
        <tr>
          <td width="50%">
            <h2 class="page-header">
              <i class="fa fa-globe"></i> {{$title}}<br>
              <small>Date: @if(Request::get('from')){{date('d/m/Y',strtotime(Request::get('from')))}}@endif @if(Request::get('to')){{"-".date('d/m/Y',strtotime(Request::get('to')))}}@endif</small>
            </h2>
          </td>
          <td width="50%">
            <img src="@if(!empty($logo)){{asset($logo)}}@endif" width="120" class="pull-right">
          </td>
        </tr>
      </table>

      @include('report_ar_invoice')

      </section>

  </body>
  </html>