@extends('layouts.app')

@section('htmlheader_title')
    Edit Invoice
@endsection

@section('contentheader_title')
    Edit Invoice
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
        <li class="active">Edit Invoice</li>
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
                    <li><a href="{{route('invoice.index')}}" >Lists</a></li>
                    <li class="active"><a href="#">Edit Invoice</a></li>
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
                        <form action="{{route('invoice.updateinvdata')}}" method="POST">
                            <div class="row" style="margin-top:30px">
                                <div class="col-sm-12">
                                    <table id="tableDetail" width="100%" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td width="100">Start</td>
                                                <td width="100">End</td>
                                                <td>Deskripsi</td>
                                                <td width="100">Amount (IDR)</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($detail as $dt){ ?>
                                            <tr>
                                                <td>
                                                    <input type="text" name="start[]" class="form-control" value="<?php echo (float)$dt->meter_start ?>">
                                                </td>
                                                <td>
                                                    <input type="text" name="end[]" class="form-control" value="<?php echo (float)$dt->meter_end ?>">
                                                </td>
                                                <td>
                                                    <textarea name="desc[]" class="form-control" rows="4" readonly>{!!$dt->invdt_note!!}</textarea>
                                                </td>
                                                <td>
                                                    <input type="text" name="amount[]" class="form-control" readonly value="<?php echo (float)$dt->invdt_amount ?>">
                                                </td>
                                            </tr>
                                            <?php $ket = explode('<br>', $dt->invdt_note); ?>
                                            <input type="hidden" name="costd_id[]" value="<?php echo $dt->costd_id; ?>">
                                            <input type="hidden" name="note[]" value="<?php echo $ket[0]; ?>">
                                            <?php } ?>
                                            <input type="hidden" name="id" value="<?php echo $ids; ?>">
                                        </tbody>
                                    </table>
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
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>

<script type="text/javascript">
$(function(){
    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".js-example-basic-single").select2();

    $(document).delegate('.amount', 'keypress', function(key) {
        // if(key.charCode < 48 || key.charCode > 57) return false;
    });

    $(document).delegate('.qty,.amount,.ppn,.discount', 'change', function(){
        countSubtotal($(this).parents('tr'));
    });

    $(document).delegate('.removeRow','click',function(){
        if(confirm('Are you sure want to remove this?')){
            $(this).parent().parent().remove();
            //countTotal();
        }
    });

    var coacode, desc, priceeach, dept, qty, useppn, useppnval, ppnamount, subtotal, acctype;
   
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
        $('#tableDetail tbody').append('<tr id="rowEmpty"><td colspan="6"><center>Data Kosong</center></td></tr>');
    }
    return subtotal;
}

</script>
@endsection