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
        <li><a href="{{route('payable.index')}}" >Lists</a></li>
        <li><a href="{{route('payable.withoutpo')}}">Non PO</a></li>
        <li class="active"><a href="#">With PO</a></li>
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
                <form action="{{route('payable.withpo')}}" method="POST">
                    <div class="row">
                      <div class="col-sm-6">
                          <label>Purchase Order</label>
                            <select name="po_id" class="choose-po" id="select2PO" style="width:100%" required="">
                            </select>
                          
                      </div>
                    </div>

                    <div class="row" style="margin-top: 30px">
                        
                        <div class="col-sm-3">
                          <div class="form-group">
                              <label>Supplier</label>
                              <select name="spl_id" class="form-control" disabled required="">
                                  <option value="">-</option>
                                  @foreach($suppliers as $val)
                                  <option value="{{$val->id}}">{{$val->spl_name}}</option>
                                  @endforeach
                              </select>
                          </div>
                         </div>

                         <div class="col-sm-3">
                          <div class="form-group">
                              <label>Phone</label>
                              <input type="text" class="form-control" name="spl_phone" disabled="">
                          </div>
                         </div>

                         <div class="col-sm-3">
                          <div class="form-group">
                              <label>Address</label>
                              <textarea class="form-control" name="spl_address" disabled=""></textarea>
                          </div>
                         </div>

                         <div class="col-sm-3">
                             <label>Terms</label>
                                <select class="form-control" name="terms" disabled="">
                                    @foreach($payment_terms as $term)
                                    <option>{{$term->name}}</option>
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
                                <input type="text" name="invoice_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" disabled="">
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
                                <input type="text" name="invoice_duedate" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd" disabled="">
                              </div>
                          </div>
                       </div>

                       <div class="col-sm-3">
                        <div class="form-group">
                            <label>Transaction No</label>
                            <input class="form-control" name="invoice_no" type="text" value="{{$inv_number}}" required>
                        </div>
                       </div>

                       <div class="col-sm-3">
                        <div class="form-group">
                            <label>Note</label>
                            <textarea class="form-control" name="hdnote"></textarea>
                        </div>
                       </div>

                    </div>
                    <hr>
                    

                    <div class="row" style="margin-top:30px">
                        <div class="col-sm-12">
                          <table id="tableDetail" width="100%" class="table table-bordered">
                            <thead>
                            <tr>
                              <td>Description</td>
                              <td>Account</td>
                              <td width="70">Qty</td>
                              <td width="200">Amount (IDR)</td>
                              <td width="150">Account Type</td>
                              <!-- <td width="150">Pajak</td> -->
                              <td>Dept</td>
                              <td>Total</td>
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
                          Total : <b id="subtotalAmount">0</b><br>
                          <!-- Tax : <b id="ppnAmount">0</b><br>
                          Total Amount : <b id="totalAmount">0</b> -->
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

    $(".choose-po").select2({
              ajax: {
                url: "{{route('po.select2')}}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    q: params.term, // search term
                    page: params.page
                  };
                },
                
                cache: true
              },
              escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
              // minimumInputLength: 1
        }).on("select2:select", function(e) {
             $('#tableDetail tbody').html('');
           $.post('{{route('po.ajax')}}', {id: $(this).val()}, function(result){
              $("select[name=spl_id]").val(result.spl_id).trigger('change');
              $("select[name=terms]").val(result.terms);
              $("input[name=invoice_date]").val(result.po_date);
              $("input[name=invoice_duedate]").val(result.due_date);

              $('#rowEmpty').remove();
              var total = 0, totalppn = 0;
              $.each(result.detail, function (index, item) {
                  var subtotal = item.qty * item.amount;
                  if(item.coa_type == "DEBET") total += item.qty * item.amount;
                  else total -= item.qty * item.amount;
                  totalppn += parseFloat(item.ppn_amount);
                  $('#tableDetail tbody').append('<tr><td>'+item.note+'</td><td>'+item.coa_code+'</td><td>'+item.qty+'</td><td>'+item.amount+'</td><td>'+item.coa_type+'</td><td>'+item.dept_id+'</td><td class="subtotal">'+subtotal+'</td></tr>');
              });
              $('#subtotalAmount').text(total);
              // $('#ppnAmount').text(totalppn);
              // $('#totalAmount').text(total + totalppn);
           }, 'json');
        });

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
        if(key.charCode < 48 || key.charCode > 57) return false;
    });

    $(document).delegate('.qty,.amount,.ppn', 'change', function(){
        countSubtotal($(this).parents('tr'));
    });

    $(document).delegate('.removeRow','click',function(){
          if(confirm('Are you sure want to remove this?')){
              $(this).parent().parent().remove();
              countTotal();
          }
     });

    var coacode, desc, priceeach, dept, qty, useppn, useppnval, ppnamount, subtotal;
    var ppncoa = '<select class="form-control ppn" name="ppn_coa_code[]"><option value="" data-val="0">No PPN</option>@foreach($ppn_options as $ppn) <option value="{{$ppn->coa_code}}" data-val="{{$ppn->amount}}">{{$ppn->name}}</option> @endforeach</select>';
    $("#addAccount").click(function(){
        coacode = $('#selectAccount option:selected').val();
        desc = $('#note').val();
        dept = $('#addDept').val();
        deptname = $('#addDept option:selected').text();
        
        
        if(coacode != "" && desc != "" && dept != ""){
            $('#rowEmpty').remove();

            $('#tableDetail tbody').append('<tr><td><input type="hidden" name="note[]" class="form-control" value="'+desc+'">'+desc+'</td><td><input type="hidden" name="coa_code[]" class="form-control" value="'+coacode+'">'+coacode+'</td><td><input type="number" name="qty[]" value="1" class="form-control qty"></td><td><input type="text" name="amount[]" class="form-control amount" value="1"></td><td>'+ppncoa+'<input type="hidden" name="ppn_amount[]" value="0" class="ppnamount"></td><td><input type="hidden" name="dept_id[]" value="'+dept+'">'+deptname+'</td><td class="subtotal">1</td><td><a class="removeRow"><i class="fa fa-times text-danger"></i></a></td></tr>');
            countTotal();
        }else{
            alert('Please fill COA, description, price each and department');
        }
    });
});

function countSubtotal(parent){
    var qty = parent.find('.qty').val();
    var each = parent.find('.amount').val();
    parent.find('.subtotal').text(qty * each);
    countTotal();
}

function countTotal()
{
    var total = 0, subtotal = 0, totaltax = 0;
    if($('#tableDetail tbody tr').length > 0){
        $('#tableDetail tbody tr').each(function(){
            var amount = parseFloat($(this).find('.amount').val());
            var qty = parseFloat($(this).find('.qty').val());
            var tax = parseFloat($(this).find('.ppn option:selected').data('val'));
            $(this).find('.ppnamount').val(tax * amount * qty);
            subtotal += amount * qty;
            totaltax += tax * amount * qty;
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