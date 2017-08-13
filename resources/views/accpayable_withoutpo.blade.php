@extends('layouts.app')

@section('htmlheader_title')
    Account Payable
@endsection

@section('contentheader_title')
   Account Payable
@endsection

@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <style>
    .datagrid-wrap{
        height: 400px;
    }
    .datepicker{z-index:999 !important;}

    .pagination > li > span{
      padding-bottom: 9px;
    }

    .loadingScreen{
        position: absolute;
        width: 100%;
        height: 100%;
        background: black;
        z-index: 100;
        background: rgba(204, 204, 204, 0.5);
    }
    </style>
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Account Payable without PO</li>
    </ol>
@stop

@section('main-content')
<div class="row">
      <div class="col-md-12">
        <div class="loadingScreen" style="display:none">
            <h3 style="line-height: 400px; text-align: center;">LOADING</h3>
        </div>
    <!-- Tabs -->
    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs">
        <li><a href="{{route('payable.index')}}" data-toggle="tab">Lists</a></li>
        <li class="active"><a href="#">AP without PO</a></li>
        <li><a href="{{route('payable.withpo')}}">AP with PO</a></li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <!-- template tabel -->

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

                <!-- form -->
                <form action="{{route('payable.withoutpo')}}" method="POST">
                    <div class="row">
                        
                        <div class="col-sm-4">
                          <div class="form-group">
                              <label>Supplier</label>
                              <select name="spl_id" class="form-control" required="">
                                  <option value="">-</option>
                                  @foreach($suppliers as $val)
                                  <option value="{{$val->id}}">{{$val->spl_name}}</option>
                                  @endforeach
                              </select>
                          </div>
                         </div>

                         <div class="col-sm-4">
                          <div class="form-group">
                              <label>Phone</label>
                              <input type="text" class="form-control" name="spl_phone" disabled="">
                          </div>
                         </div>

                         <div class="col-sm-4">
                          <div class="form-group">
                              <label>Address</label>
                              <textarea class="form-control" name="spl_address" disabled=""></textarea>
                          </div>
                         </div>

                    </div>
                    

                    <div class="row">
                        <div class="col-sm-3">
                          <div class="form-group">
                              <label>Transaction Date</label>
                              <div class="input-group date">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="invoice_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                              </div>
                          </div>
                       </div>

                       <div class="col-sm-3">
                          <div class="form-group">
                              <label>Due Date</label>
                              <div class="input-group date">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="invoice_duedate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                              </div>
                          </div>
                       </div>

                       <div class="col-sm-3">
                        <div class="form-group">
                            <label>Transaction No</label>
                            <input class="form-control" name="invoice_no" type="text" required>
                        </div>
                       </div>

                       <div class="col-sm-3">
                        <div class="form-group">
                            <label>Note</label>
                            <textarea class="form-control" name="note"></textarea>
                        </div>
                       </div>

                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" id="note"></textarea>
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" class="form-control" id="qty" value="1">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Price Each</label>
                                <input type="text" class="form-control" id="priceeach">
                                <label>Subtotal</label>
                                <input type="text" class="form-control" id="pricesubtotal" disabled="">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="dept_id" id="addDept" class="form-control">
                                    <option value="">Choose Department</option> 
                                    @foreach($departments as $dept)
                                    <option value="{{$dept->id}}">{{$dept->dept_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                         <div class="col-sm-3">
                            <div class="form-group">
                                <label>Use PPN ?</label><br>
                                <input type="radio" name="ppnflag" value="1">&nbsp;Yes&nbsp;&nbsp;
                                <input type="radio" name="ppnflag" value="0" checked="">&nbsp;No
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="form-group">
                                <label>PPN Amount</label><br>
                                <input type="text" class="form-control" id="ppnamount" value="0" disabled="">
                            </div>
                        </div>

                        <div class="col-sm-6">
                          <label>COA</label>
                          <div class="input-group input-group-md">
                            <select class="js-example-basic-single" id="selectAccount" style="width:100%">
                              <option value="">Choose Account</option>
                              @foreach($accounts as $key => $coa)
                                  <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                              @endforeach
                            </select>
                            <span class="input-group-btn">
                              <button type="button" id="addAccount" class="btn btn-default">Add Line</button>
                            </span>
                          </div>
                        </div>   

                    </div>

                    <div class="row" style="margin-top:30px">
                        <div class="col-sm-12">
                          <table id="tableDetail" width="100%" class="table table-bordered">
                            <thead>
                            <tr>
                              <td>Description</td>
                              <td>Account</td>
                              <td>Qty</td>
                              <td>Amount (IDR)</td>
                              <td>PPN</td>
                              <td>PPN Amount</td>
                              <td>Dept</td>
                              <td>Total</td>
                              <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr id="rowEmpty">
                              <td colspan="9"><center>Data Kosong</center></td>
                            </tr>
                            </tbody>
                          </table>

                          <br><br>
                          <h4 class="pull-right">
                          Subtotal : <b id="subtotalAmount">0</b><br>
                          Tax (PPN) : <b id="ppnAmount">0</b><br>
                          Total Amount : <b id="totalAmount">0</b>
                          </h4>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-info pull-right">Submit</button>
                        </div>
                    </div>
                    
                </form>
                <!-- form -->

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
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
$(function(){
    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".js-example-basic-single").select2();

    $("select[name=spl_id]").change(function(){
        if($(this).val() == ''){
            $('input[name=spl_phone]').val('');
            $('textarea[name=spl_address]').val('');
        }else{
            $.post("{{route('supplier.ajaxget')}}", {id: $(this).val()}, function(result){
                if(result.errorMsg) $.messager.alert('Warning',result.errorMsg);
                if(result.success){ 
                    $('input[name=spl_phone]').val(result.data.spl_phone);
                    $('textarea[name=spl_address]').val(result.data.spl_address+", "+result.data.spl_city+", "+result.data.spl_postal_code);
                }
            });
        }
    });

    $('input[name=ppnflag]').change(function(){
        if($(this).val() == "1"){
            $('#ppnamount').removeAttr('disabled');
        }else{
            $('#ppnamount').attr('disabled','disabled');
            $('#ppnamount').val('0');
        }
    });

    $('#priceeach,#ppnamount').on('keypress', function(key) {
        if(key.charCode < 48 || key.charCode > 57) return false;
    });

    $('#qty,#priceeach').change(function(){
        countSubtotal();
    });

    var coacode, desc, priceeach, dept, qty, useppn, useppnval, ppnamount, subtotal;
    $("#addAccount").click(function(){
        coacode = $('#selectAccount option:selected').val();
        desc = $('#note').val();
        priceeach = $('#priceeach').val();
        dept = $('#addDept').val();
        qty = $('#qty').val();
        useppnval = $('input[name=ppnflag]:checked').val();
        ppnamount = parseFloat($('#ppnamount').val());
        if(useppnval == 1) useppn = 'yes';
        else useppn = 'no';
        deptname = $('#addDept option:selected').text();
        subtotal = parseFloat($('#pricesubtotal').val());
        subtotal += ppnamount;
        
        if(coacode != "" && desc != "" && priceeach != "" && dept != ""){
            $('#rowEmpty').remove();
            $('#tableDetail tbody').append('<tr><td><input type="hidden" name="note[]" class="form-control" value="'+desc+'">'+desc+'</td><td><input type="hidden" name="coa_code[]" class="form-control" value="'+coacode+'">'+coacode+'</td><td><input type="hidden" name="qty[]" class="form-control qty" value="'+qty+'">'+qty+'</td><td><input type="hidden" name="amount[]" class="form-control amount" value="'+priceeach+'">'+priceeach+'</td><td><input type="hidden" name="is_ppn[]" class="form-control" value="'+useppnval+'">'+useppn+'</td><td><input type="hidden" name="ppn_amount[]" class="form-control ppn" value="'+ppnamount+'">'+ppnamount+'</td><td><input type="hidden" name="dept_id[]" value="'+dept+'">'+deptname+'</td><td>'+subtotal+'</td><td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
            countTotal();
        }else{
            alert('Please fill COA, description, price each and department');
        }
    });
});

function countSubtotal(){
    var qty = $('#qty').val();
    var each = $('#priceeach').val();
    $('#pricesubtotal').val(qty * each);
}

function countTotal()
{
    var total = 0, subtotal = 0, totaltax = 0;
    if($('#tableDetail tbody tr').length > 0){
        $('#tableDetail tbody tr').each(function(){
            var amount = parseFloat($(this).find('.amount').val());
            var qty = parseFloat($(this).find('.qty').val());
            var tax = parseFloat($(this).find('.ppn').val());
            subtotal += amount * qty;
            totaltax += tax;
        });
        $('#subtotalAmount').text(subtotal);
        $('#ppnAmount').text(totaltax);
        $('#totalAmount').text(subtotal + totaltax);
    }else{
        $('#tableDetail tbody').append('<tr id="rowEmpty"><td colspan="6"><center>Data Kosong</center></td></tr>');
    }
}

</script>
@endsection