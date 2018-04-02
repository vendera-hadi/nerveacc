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
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Other Config</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">
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

                                <div class="form-group">
                                  <label>Service Charge Alias</label>
                                  <input type="text" name="service_charge_alias" class="form-control" value="{{$service_charge}}">
                                </div>

                                <div class="form-group">
                                  <label>Due Date Invoice (every month)</label>
                                  <input type="number" name="duedate_interval" class="form-control" value="{{$duedate}}">
                                </div>

                                <div class="form-group">
                                  <label>Invoice start Date (every month)</label>
                                  <input type="number" min="1" max="31" name="invoice_startdate" class="form-control" value="{{$invoice_startdate}}">
                                </div>

                                <div class="form-group">
                                  <label>PPJU</label>
                                  <input type="number" min="1" max="31" name="ppju" class="form-control" value="{{$ppju}}" placeholder="Percentage">
                                </div>

                                @if(!empty($signature))
                                <div class="form-group">
                                  <label>Current Signature</label><br>
                                  <img src="{{asset($signature)}}" class="img-responsive" width="150">
                                </div>
                                @endif

                                <div class="form-group">
                                  <label>Digital Signature</label>
                                  <input type="file" name="digital_signature" class="form-control" accept="image/jpeg,image/jpg,image/png">
                                </div>

                                <div class="form-group">
                                  <label>CC Email Address</label>
                                  <input type="text" name="cc_email" class="form-control" value="{{$cc_email}}">
                                </div>

                                <div class="form-group">
                                  <label>Send Invoice Email</label><br>
                                  <input type="radio" name="send_inv_email" value="1" @if($sendEmail==1) checked @endif> &nbsp; Active &nbsp;&nbsp;
                                  <input type="radio" name="send_inv_email" value="0" @if($sendEmail==0) checked @endif> &nbsp; Not Active
                                </div>

                                <div class="form-group">
                                  <label>Active Invoice Signature</label><br>
                                  <input type="radio" name="invoice_signature_flag" value="1" @if($signatureFlag==1) checked @endif> &nbsp; Active &nbsp;&nbsp;
                                  <input type="radio" name="invoice_signature_flag" value="0" @if($signatureFlag==0) checked @endif> &nbsp; Not Active
                                </div>

                                <div class="form-group">
                                  <label>Prefix Kuitansi (max 3 Character)</label>
                                  <input type="text" name="prefix_kuitansi" class="form-control" value="{{$prefixKuitansi}}" maxlength="3">
                                </div>

                                <div class="form-group">
                                  <label>Invoice Email Template</label>
                                  <textarea class="textarea" name="inv_body_email" class="form-control" style="width: 100%;" required>{{$invBodyEmail}}</textarea>
                                </div>

                                <div class="form-group">
                                  <label>Footer PO Text</label>
                                  <textarea class="textarea" name="footer_po" class="form-control" style="width: 100%;" required>{{$footer_po}}</textarea>
                                </div>

                                <div class="form-group">
                                  <label>Footer PO Label</label>
                                  <textarea class="textarea" name="footer_label_po" class='form-control' style="width: 100%;" required>{{$label_po}}</textarea>
                                </div>

                                <div class="form-group">
                                  <label>PO Signature Name</label>
                                  <input type="text" name="footer_signature_name" class="form-control" value="{{$footer_signature_name}}">
                                </div>

                                <div class="form-group">
                                  <label>PO Signature Position</label>
                                  <input type="text" name="footer_signature_position" class="form-control" value="{{$footer_signature_position}}">
                                </div>

                                <div class="form-group">
                                  <label>PO Prefix</label>
                                  <input type="text" name="po_prefix" class="form-control" value="{{$po_prefix}}">
                                </div>

                                <div class="form-group">
                                  <label>COA Hutang Titipan di fitur Payment (wajib isi)</label>
                                  <select name="coa_hutang_titipan" style="width:100%">
                                    <option value="">Choose Account</option>
                                    @foreach($accounts as $key => $coa)
                                        <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}" @if($coa_hutang_titipan == $coa->coa_code) selected @endif>{{$coa->coa_code." ".$coa->coa_name}}</option>
                                    @endforeach
                                  </select>
                                </div>

                                <div class="form-group">
                                  <label>COA Code Laba Rugi Berjalan (wajib isi)</label>
                                  <select name="coa_laba_rugi" id="selectAccount" style="width:100%">
                                    <option value="">Choose Account</option>
                                    @foreach($accounts as $key => $coa)
                                        <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}" @if($coa_laba_rugi == $coa->coa_code) selected @endif>{{$coa->coa_code." ".$coa->coa_name}}</option>
                                    @endforeach
                                  </select>
                                </div>

                            </div>
                          </div>
                          <!-- /.box-body -->

                          <div class="box-footer">
                            <button type="submit" class="btn btn-flat btn-primary">Submit</button>
                          </div>
                        </form>


                      </div>
                </div>
            </div>
        <!-- content -->
    </div>
</div>
@endsection

@section('footer-scripts')
<script type="text/javascript" src="{{asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
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
     $(".js-example-basic-single").select2();
});
</script>
@endsection