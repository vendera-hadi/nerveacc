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
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li><a href="{{route('payable.index')}}" >Lists</a></li>
                    <li class="active"><a href="#">Non PO</a></li>
                    <li><a href="{{route('payable.withpo')}}">With PO</a></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
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

                    <form action="{{route('payable.updatewithoutpo')}}" method="POST">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <select name="spl_id" class="form-control" required="">
                                        <option value="">-</option>
                                        @foreach($suppliers as $val)
                                        <option value="{{$val->id}}" <?php echo ($header->spl_id == $val->id ? 'selected' : ''); ?>>{{$val->spl_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" class="form-control" name="spl_phone" disabled="" value="<?php echo $supps->spl_phone; ?>">
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea class="form-control" name="spl_address" disabled=""><?php echo $supps->spl_address.', '.$supps->spl_city.', '.$supps->spl_postal_code; ?></textarea>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <label>Terms</label>
                                <select class="form-control" name="terms">
                                    @foreach($payment_terms as $term)
                                    <option <?php echo ($header->terms == $term->name ? 'selected' : ''); ?>>{{$term->name}}</option>
                                    @endforeach
                                </select>
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
                                        <input type="text" name="invoice_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $header->invoice_date; ?>">
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
                                        <input type="text" name="invoice_duedate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" value="<?php echo $header->invoice_duedate; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Transaction No</label>
                                    <input class="form-control" name="invoice_no" type="text" required value="<?php echo $header->invoice_no; ?>">
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea class="form-control" name="hdnote"><?php echo $header->note; ?></textarea>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" id="note"></textarea>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" id="coatype">
                                      <option>DEBET</option>
                                      <option>KREDIT</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-4">
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

                            <div class="col-sm-12">
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
                                        <td width="70">Qty</td>
                                        <td width="200">Amount (IDR)</td>
                                        <td width="200">Discount (IDR)</td>
                                        <td width="150">Account Type</td>
                                        <td>Dept</td>
                                        <td>Total</td>
                                        <td>Action</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach($detail as $row){
                                        echo '<tr>
                                        <td><input type="hidden" name="note[]" class="form-control" value="'.$row->note.'">'.$row->note.'</td>
                                        <td><input type="hidden" name="coa_code[]" class="form-control" value="'.$row->coa_code.'">'.$row->coa_code.'</td>
                                        <td><input type="number" name="qty[]" value="'.$row->qty.'" class="form-control qty"></td>
                                        <td><input type="text" name="amount[]" class="form-control amount" value="'.(int)$row->amount.'"></td>
                                        <td><input type="text" class="form-control discount" name="discount[]" value="'.(int)$row->discount.'"></td>
                                        <td>'.$row->coa_type.'<input type="hidden" name="coa_type[]" value="'.$row->coa_type.'" class="type"></td>
                                        <td><input type="hidden" name="dept_id[]" value="'.$row->dept_id.'">'.$row->dept_name.'</td>
                                        <td class="subtotal">'.(int)$row->final_total.'</td><td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td>
                                        </tr>';
                                    } ?>
                                </tbody>
                            </table>

                            <br><br>
                            <h4 class="pull-right">
                                Total : <b id="subtotalAmount"><?php echo (int)$header->total; ?></b><br>
                                <input type="hidden" name="ap_id" value="<?php echo $header->id; ?>">
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

    $(document).delegate('.amount', 'keypress', function(key) {
        // if(key.charCode < 48 || key.charCode > 57) return false;
    });

    $(document).delegate('.qty,.amount,.ppn,.discount', 'change', function(){
        countSubtotal($(this).parents('tr'));
    });

    $(document).delegate('.removeRow','click',function(){
          if(confirm('Are you sure want to remove this?')){
              $(this).parent().parent().remove();
              countTotal();
          }
     });

    var coacode, desc, priceeach, dept, qty, useppn, useppnval, ppnamount, subtotal, acctype;
    var ppncoa = '<select class="form-control ppn" name="ppn_coa_code[]"><option value="" data-val="0">No PPN</option>@foreach($ppn_options as $ppn) <option value="{{$ppn->coa_code}}" data-val="{{$ppn->amount}}">{{$ppn->name}}</option> @endforeach</select>';
    $("#addAccount").click(function(){
        coacode = $('#selectAccount option:selected').val();
        desc = $('#note').val();
        dept = $('#addDept').val();
        deptname = $('#addDept option:selected').text();
        acctype = $('#coatype').val();

        if(coacode != "" && desc != "" && dept != ""){
            $('#rowEmpty').remove();

            $('#tableDetail tbody').append('<tr><td><input type="hidden" name="note[]" class="form-control" value="'+desc+'">'+desc+'</td><td><input type="hidden" name="coa_code[]" class="form-control" value="'+coacode+'">'+coacode+'</td><td><input type="number" name="qty[]" value="1" class="form-control qty"></td><td><input type="text" name="amount[]" class="form-control amount" value="1"></td><td><input type="text" class="form-control discount" name="discount[]" value="0"></td><td>'+acctype+'<input type="hidden" name="coa_type[]" value="'+acctype+'" class="type"></td><td><input type="hidden" name="dept_id[]" value="'+dept+'">'+deptname+'</td><td class="subtotal">1</td><td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
            countTotal();
        }else{
            alert('Please fill COA, description, price each and department');
        }
    });
});

function countSubtotal(parent){
    var qty = parent.find('.qty').val();
    var each = parent.find('.amount').val();
    var coatype = parent.find('.type').val();
    var discount = parent.find('.discount').val();
    parent.find('.subtotal').text((qty * each) - discount);
    if(countTotal() < 0){
      // alert('Total kurang dari 0, harap atur ulang qty');
      parent.find('.qty').val(1).trigger('change');
    }
}

function countTotal()
{
    var total = 0, subtotal = 0, totaltax = 0;
    if($('#tableDetail tbody tr').length > 0){
        $('#tableDetail tbody tr').each(function(){
            var amount = parseFloat($(this).find('.amount').val());
            var qty = parseFloat($(this).find('.qty').val());
            var coatype = $(this).find('.type').val();
            var discount = parseFloat($(this).find('.discount').val());
            // var tax = parseFloat($(this).find('.ppn option:selected').data('val'));
            // $(this).find('.ppnamount').val(tax * amount * qty);
            if(coatype == 'KREDIT') subtotal += -1 * ((amount * qty) - discount);
            else subtotal += (amount * qty) - discount;
            // totaltax += tax * amount * qty;
        });
        $('#subtotalAmount').text(subtotal);
        // $('#ppnAmount').text(totaltax);
        // $('#totalAmount').text(subtotal + totaltax);
    }else{
        $('#tableDetail tbody').append('<tr id="rowEmpty"><td colspan="9"><center>Data Kosong</center></td></tr>');
    }
    return subtotal;
}

</script>
@endsection