@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Fitting Out
@endsection

<!-- page title -->
@section('contentheader_title')
   Fitting Out
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">
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
        <li class="active">Fitting Out</li>
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
                <li class="active"><a href="#tab_1" data-toggle="tab">Lists</a></li>
                @if(Session::get('role')==1 || in_array(78,Session::get('permissions')))
                <li><a href="#tab_2" data-toggle="tab">Add Fitting In</a></li>
                @endif
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                <!-- template tabel -->
                    <table id="dg" title="Fitting Out" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                        <!-- kolom -->
                        <thead>
                            <tr>
                                <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                                <th field="checkbox" width="20"></th>
                                <th field="unit_code" width="30" sortable="true">Unit</th>
                                <th field="tenan_name" width="100" sortable="true">Tenant</th>
                                <th field="fit_number" width="60" sortable="true">Nomor</th>
                                <th field="fit_date" width="40" sortable="true">Date</th>
                                <th field="fit_amount" width="50" sortable="true">Amount</th>
                                <th field="fit_refno" width="50" sortable="true">RefNo</th>
                                <th field="posting_at" width="40" sortable="true">Tgl Posting</th>
                                <th field="fit_post" width="30" sortable="true">Posting</th>
                                <th field="out_number" width="50" sortable="true">OutNumber</th>
                                <th field="action_button" width="40">Action</th>
                            </tr>
                        </thead>
                    </table>
                    <!-- end table -->
                    <!-- icon2 atas table -->
                    <div id="toolbar" class="datagrid-toolbar">
                        <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span>
                        </label>
                        @if(Session::get('role')==1 || in_array(78,Session::get('permissions')))
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posting Selected Fitting In</span>
                        </a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="unpostingInv()" group="" id=""><span class="l-btn-text"><i class="fa fa-remove"></i>&nbsp;Unposting Selected Fitting In</span>
                        </a>
                        @endif
                    </div>
                    <div id="dlg" class="easyui-dialog" style="width:40%"
                        closed="true" buttons="#dlg-buttons">
                        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:10px">
                                <input type="text" class="easyui-datebox" required="required" name="posting_date" label="Posting Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                            </div>
                        </form>
                    </div>
                    <div id="dlg-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="savepayment()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                    </div>

                    <!-- end icon -->
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_2">
                    <div id="contractStep1">
                        <form method="POST" id="formPayment">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Unit Code</label>
                                        <select class="form-control contrId choose-contract" name="tenan_id" style="width:100%" id="slc">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Nama Tenan</label>
                                        <input class="form-control" name="tenan_name" readonly id="nmtenan">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Bank</label>
                                        <select class="form-control cashbkId choose-style" name="cashbk_id" style="width:100%">
                                            <?php foreach ($cashbank_data as $key => $value) { ?>
                                            <option value="<?php echo $value['id']?>"><?php echo $value['cashbk_name']?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input type="text" name="fit_amount" class="form-control" value="3200000">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Fitting In Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                            <input type="text" id="FittingInDate" name="fit_date" required="required" class="form-control pull-right datepicker" data-date-format="yyyy-mm-dd">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Ref Number</label>
                                        <input type="text" name="fit_refno" class="form-control" placeholder="Nomor Referensi Bank">
                                        <input type="hidden" name="upd_id" class="form-control" value="0">
                                        <input type="hidden" name="unt_id" class="form-control" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea class="textarea" name="fit_keterangan" class="form-control" style="width: 100%;" id="ctbodysp">1. DEPOSIT FIT OUT SEBESAR TIGA JUTA RUPIAH<br>2. BIAYA ADM FIT OUT  SEBESAR DUA RATUS RIBU RUPIAH<br>TRANSFER AN XXXXX</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <?php 
                                    session_start();
                                    $secret=md5(uniqid(rand(), true));
                                    Session::set('FORM_SECRET', $secret); 
                                    ?>
                                    <input type="hidden" id="form_secret" name="form_secret" value="<?php echo $secret; ?>">
                                </div>
                            </div>
                            <button type="submit" id="submitForm" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- content -->

        <!-- Modal extra -->
        <div id="detailModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Fitting In Information</h4>
                    </div>
                    <div class="modal-body" id="detailModalContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->

        <!-- Modal select unit -->
        <div id="unitModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-body" id="unitModalContent"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Modal -->
    <!-- content -->
    </div>
</div>

@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript" src="{{asset('plugins/bootstrap-wysihtml5/dist/bootstrap3-wysihtml5.all.js')}}"></script>

<script type="text/javascript">
    var entity = "Fitting In"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('fittingin.get')}}";
    $('.select2').select2();
    $("#ctbodysp").wysihtml5();

    $(function(){
        var dg = $('#dg').datagrid({
            url: get_url,
            pagination: true,
            remoteFilter: true,
            rownumbers: true,
            singleSelect: true,
            fitColumns: true,
            pageSize:100,
            pageList: [100,500,1000],
            onLoadSuccess:function(target){
                print_window();
            }
        });
        dg.datagrid('enableFilter');
    });

    $('#formPayment').submit(function(e){
        e.preventDefault();
        $('#submitForm').attr('disabled','disabled');
        var startdate = $('#fit_date').val();

        if(startdate == ''){
            $.messager.alert('Warning','Date date must be choose');
        }else if($('#contrId').val() == ""){
          $.messager.alert('Warning','Unit must be choose');
        }else{
          var allFormData = $('#formPayment').serialize();
          var i;
          $.post('{{route('fitting.insertfittingin')}}',allFormData, function(result){
              $('#submitForm').removeAttr('disabled');
              alert(result.message);
              if(result.status == 1){
                location.reload();
              }
          });

          return false;
        }
    });

    $('.datepicker').datepicker({
        autoclose: true
    });

    $(".choose-style").select2();

    $(".choose-contract").select2({
        ajax: {
            url: "{{route('contract.optParent')}}",
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
        minimumInputLength: 0
    })

    $(".contrId").change(function(){
        var url = "{{route('fitting.get_tenant')}}";
        var val = $(this).val();

        url = url+"?tenan_id="+val;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(result) {
                $('#nmtenan').val(result);
                return false;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('failed during proccess.');
                return false;
            }
        });
    });

    $(document).delegate('.getDetail','click',function(){
        $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
        var id = $(this).data('id');
        $.post('{{route('creditnote.getdetail')}}',{id:id}, function(data){
            $('#detailModalContent').html(data);
        });
    });

    $('input[name=checkall]').change(function() {
        if($(this).is(':checked')){
            $('input[name=check]').each(function(){
                $(this).prop('checked',true);
            });
        }else{
            $('input[name=check]').each(function(){
                $(this).prop('checked',false);
            });
        }
     });

    function postingInv(){
        $('#dlg').dialog('open').dialog('center').dialog('setTitle','Posting Fitting In');
        $('#fm').form('clear');
    }

    function savepayment(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
            var data = $("#fm").serializeArray();
            data.push({name: "id", value: ids});
            $.post('{{route('fittingin.posting')}}',data, function(result){
                if(result.success == 1){
                    $.messager.alert('Success','Data saved');
                    $('#dlg').dialog('close');
                    $('#dg').datagrid('reload');
                }else{
                    $.messager.show({
                        title: 'Error',
                        msg: result.message
                    });
                }
            });
        }
    }

    function unpostingInv(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to unpost this '+ids.length+' Fitting In ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('fittingin.unposting')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        location.reload();
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

    $(document).delegate('.void-confirm','click',function(){
        if(confirm("are you sure you want void this fitting in?")){
            var url = $(this).attr('href');
            $.ajax({
                url: url,
                type: 'GET',
                success: function(result) {
                    alert(result.message);
                    if(result.status == 1) location.reload();
                    return false;
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('failed during proccess.');
                    return false;
                }
            });
            return false;
        }else{
            return false;
        }
    });

    $(document).delegate('.posting-confirm','click',function(){
        if(confirm("are you sure you want posting this fitting in?")){
            var id = $(this).attr('data-id');

            $.post('{{route('fittingin.posting')}}',{id:id}, function(data){
                alert(data.message);
                if(data.success == 1){
                    location.reload();
                }else{
                    return false;
                }
            });
        }else{
            return false;
        }
    });

    $(document).delegate('.paid-amount','change',function(){
        var maxVal = parseFloat($(this).attr('maxlength'));
        var currentVal = parseFloat($(this).val());
        if(currentVal > maxVal) $(this).val(maxVal);
        if(currentVal < 1) $(this).val(1);
    });

    $(document).delegate('.paid-check','change',function(){
        if($(this).is(':checked')) $(this).parents('tr').find('.paid-amount').removeAttr('disabled');
        else $(this).parents('tr').find('.paid-amount').attr('disabled','disabled');
    });

    var print_window = function(){
        $('.print-window').off('click');
        $('.print-window').click(function(){
            var self = $(this);
            var url = self.attr('href');
            var title = self.attr('title');
            var w = self.attr('data-width');
            var h = self.attr('data-height');

            openWindow(url, title, w, h);

            return false;
        });
    };

    function openWindow(url, title, w, h){
        // Fixes dual-screen position                         Most browsers      Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;
        var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

        // Puts focus on the newWindow
        if (window.focus) {
            newWindow.focus();
        }
    }

    function myformatter(date){
        var y = date.getFullYear();
        var m = date.getMonth()+1;
        var d = date.getDate();
        return (d<10?('0'+d):d)+'-'+(m<10?('0'+m):m)+'-'+y;
    }

    function myparser(s){
        if (!s) return new Date();
        var ss = (s.split('-'));
        var y = parseInt(ss[0],10);
        var m = parseInt(ss[1],10);
        var d = parseInt(ss[2],10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
            return new Date(d,m-1,y);
        } else {
            return new Date();
        }
    }

    $(document).delegate('.edit-confirm','click',function(){
        var id = $(this).attr('data-id');
        var url = '{{route('fitting.editfittingout')}}';
        var editorObj = $("#ctbodysp").data('wysihtml5');
        var studentSelect = $('#slc');
        $.ajax({
            url: url,
            type: 'POST',
            data: {id:id},
            success: function(result) {
                var option = new Option(result.unit_code, result.unit_id, true, true);
                studentSelect.append(option).trigger('change');
                gttenant(result.unit_id);
                $('input[name=unt_id]').val(result.unit_id);
                $('input[name=fit_date]').val(result.fit_date);
                $('input[name=fit_amount]').val(result.fit_amount);
                $('input[name=fit_refno]').val(result.fit_refno);
                $('.cashbkId').val(result.cashbk_id).trigger('change');
                var editor = editorObj.editor;
                editor.setValue(result.fit_keterangan);
                $('input[name=upd_id]').val(id);
                $('.nav-tabs a[href="#tab_2"]').tab('show');
                return false;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('failed during proccess.');
                return false;
            }
        });
    });

    function gttenant(unitcode){
        var url = "{{route('fitting.get_tenant')}}";
        var val = unitcode;

        url = url+"?tenan_id="+val;

        $.ajax({
            url: url,
            type: 'GET',
            success: function(result) {
                $('#nmtenan').val(result);
                return false;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('failed during proccess.');
                return false;
            }
        });
    }

</script>
@endsection
