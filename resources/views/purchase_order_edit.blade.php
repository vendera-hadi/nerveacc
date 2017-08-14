@extends('layouts.app')

@section('htmlheader_title')
    Add Purchase Order
@endsection

@section('contentheader_title')
   Add Purchase Order
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
        <li class="active">Add Purchase Order</li>
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
        <li><a href="{{route('po.index')}}">Lists</a></li>
        <li class="active"><a href="#">Insert PO</a></li>
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
                <form action="" method="POST">
                    <div class="row">

                        <div class="col-sm-3">
                          <div class="form-group">
                              <label>PO Number</label>
                              <input type="text" class="form-control" value="{{$current->po_number}}" disabled="">
                          </div>
                         </div>

                         <div class="col-sm-3">
                          <div class="form-group">
                              <label>PO Date</label>
                              <div class="input-group date">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="po_date" required="required" class="form-control pull-right datepicker" value="{{$current->po_date}}" data-date-format="yyyy-mm-dd">
                              </div>
                          </div>
                       </div>
                        
                        <div class="col-sm-3">
                          <div class="form-group">
                              <label>Supplier</label>
                              <select name="spl_id" class="form-control" required="">
                                  <option value="">-</option>
                                  @foreach($suppliers as $val)
                                  <option value="{{$val->id}}" @if($current->spl_id == $val->id) selected @endif>{{$val->spl_name}}</option>
                                  @endforeach
                              </select>
                          </div>
                         </div>

                         <div class="col-sm-3">
                          <div class="form-group">
                              <label>Due Date</label>
                              <div class="input-group date">
                                <div class="input-group-addon">
                                  <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" name="invoice_duedate" value="{{$current->due_date}}" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                              </div>
                          </div>
                       </div>

                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                             <label>Terms</label>
                                <select class="form-control" name="terms">
                                    @foreach($payment_terms as $term)
                                    <option @if($current->terms == $term->name) selected @endif>{{$term->name}}</option>
                                    @endforeach
                                </select>
                         </div>

                         <div class="col-sm-3">
                            <div class="form-group">
                                <label>Note</label>
                                <textarea class="form-control" name="hdnote">{{$current->note}}</textarea>
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
                                <label>Department</label>
                                <select name="dept_id" id="addDept" class="form-control">
                                    <option value="">Choose Department</option> 
                                    @foreach($departments as $dept)
                                    <option value="{{$dept->id}}">{{$dept->dept_name}}</option>
                                    @endforeach
                                </select>
                                
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
                              <td width="70">Qty</td>
                              <td width="200">Amount (IDR)</td>
                              <td width="150">PPN</td>
                              <td>Dept</td>
                              <td>Total</td>
                              <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($current->detail as $detail)
                            <tr>
                              <td>
                                <input type="hidden" name="note[]" class="form-control" value="{{$detail->note}}">{{$detail->note}}
                              </td>
                              <td>
                                <input type="hidden" name="coa_code[]" class="form-control" value="{{$detail->coa_code}}">{{$detail->coa_code}}
                              </td>
                              <td>
                                <input type="number" name="qty[]" value="{{$detail->qty}}" class="form-control qty">
                              </td>
                              <td>
                                <input type="text" name="amount[]" class="form-control amount" value="{{(int)$detail->amount}}">
                              </td>
                              <td>
                                <select class="form-control ppn" name="ppn_coa_code[]">
                                  <option value="" data-val="0">No PPN</option>
                                  @foreach($ppn_options as $ppn)
                                  <option value="{{$ppn->coa_code}}" data-val="{{$ppn->amount}}" @if($detail->ppn_coa_code == $ppn->coa_code) selected @endif>{{$ppn->name}}</option>
                                  @endforeach
                                </select>
                                <input type="hidden" name="ppn_amount[]" value="{{$detail->ppn_amount}}" class="ppnamount">
                              </td>
                              <td>
                                <input type="hidden" name="dept_id[]" value="{{$detail->dept_id}}">{{$detail->dept->dept_name}}
                              </td>
                              <td class="subtotal">{{$detail->amount * $detail->qty}}</td>
                              <td>
                                <a class="removeRow"><i class="fa fa-times text-danger"></i></a>
                              </td>
                            </tr>
                            @endforeach
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
    countTotal();
    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".js-example-basic-single").select2();

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