@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Reminder
@endsection

<!-- page title -->
@section('contentheader_title')
   Reminder
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
        <li class="active">Reminder</li>
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
                <li><a href="#tab_4" data-toggle="tab">Body Email</a></li>
                <li><a href="#tab_2" data-toggle="tab">SP 1</a></li>
                <li><a href="#tab_3" data-toggle="tab">SP 2</a></li>
                <li><a href="#tab_5" data-toggle="tab">SP 3</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                <!-- template tabel -->
                    <table id="dg" title="Reminder List" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                        <!-- kolom -->
                        <thead>
                            <tr>
                                <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                                <th field="checkbox" width="15"></th>
                                <th field="reminder_no" sortable="true">No Reminder</th>
                                <th field="unit_code" width="40" sortable="true">No Unit</th>
                                <th field="tenan_name" width="110" sortable="true">Tenant</th>
                                <th field="tenan_phone" sortable="true">Phone</th>
                                <th field="sp_type" width="20" sortable="true">SP</th>
                                <th field="reminder_date" sortable="true">Date</th>
                                <th field="pokok_amount" width="50" sortable="true" align="right">Pokok</th>
                                <th field="denda_total" width="50" sortable="true" align="right">Denda</th>
                                <th field="denda_outstanding" width="50" sortable="true" align="right">Total</th>
                                <th field="lastsent_date" width="50" sortable="true">Last Send</th>
                                <th field="sent_counter" width="40" sortable="true">Counter</th>
                                <th field="posting" width="40" sortable="true">Posted</th>
                                <th field="action_button" width="40">Action</th>
                            </tr>
                        </thead>
                    </table>
                    <!-- end table -->
                    <!-- icon2 atas table -->
                    <div id="toolbar" class="datagrid-toolbar">
                        <label style="margin-left:10px; margin-right:5px"><input type="checkbox" name="checkall" style="vertical-align: top;margin-right: 6px;"><span style="vertical-align: middle; font-weight:400">Check All</span>
                        </label>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser2()">New Reminder All</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New Reminder Single</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="sendRmd()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Send Email</span>
                        </a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="deleteRmd()" group="" id=""><span class="l-btn-text"><i class="fa fa-remove"></i>&nbsp;Delete Reminder</span>
                        </a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="editFooter()" group="" id=""><span class="l-btn-text"><i class="fa fa-font"></i>&nbsp;Add Manual Invoice</span></a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="postRmd()" group="" id=""><span class="l-btn-text"><i class="fa fa-check"></i>&nbsp;Posted Reminder</span>
                        </a>
                        <a href="javascript:void(0)" class="easyui-linkbutton l-btn l-btn-small l-btn-plain" plain="true" onclick="unpostRmd()" group="" id=""><span class="l-btn-text"><i class="fa fa-remove"></i>&nbsp;Unposted Reminder</span>
                        </a>
                    </div>
                    <!-- end icon -->

                    <!-- hidden form buat create edit -->
                    <div id="dlg" class="easyui-dialog" style="width:60%"
                            closed="true" buttons="#dlg-buttons">
                        <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                            <div style="margin-bottom:10px">
                                <input type="text" class="easyui-datebox" required="required" name="reminder_date" label="Reminder Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                            </div>
                            <div style="margin-bottom:10px">
                                <select id="jenissp" class="easyui-combobox" required="true" name="sp_type" label="Jenis SP:" style="width:300px;">
                                    <option value="4">SP 1</option>
                                    <option value="5">SP 2</option>
                                    <option value="6">SP 3</option>
                                </select>
                            </div>
                            <div style="margin-bottom:10px">
                                <label class="textbox-label textbox-label-before" for="_easyui_textbox_input10" style="text-align: left; height: 27px; line-height: 27px;">No Unit</label>
                                <select id="unit" name="unit_id" style="width: 60%; height: 30px; border-radius: 4px; border-color: #95B8E7;" required>
                                    @foreach($unit as $tent)
                                    <option value="{{$tent->id}}" data-owner="{{$tent->unit_code}}">{{$tent->unit_code}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <div id="dlg-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
                    </div>
                    <!-- end form -->
                    <div id="dlg2" class="easyui-dialog" style="width:60%" closed="true" buttons="#dlg2-buttons">
                        <form id="fm2" method="post" novalidate style="margin:0;padding:20px 50px">
                            <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                            <div style="margin-bottom:10px">
                                <input type="text" class="easyui-datebox" required="required" name="reminder_date" label="Reminder Date :" style="width:100%" data-options="formatter:myformatter,parser:myparser">
                            </div>
                            <div style="margin-bottom:10px">
                                <select id="jenissp" class="easyui-combobox" required="true" name="sp_type" label="Jenis SP:" style="width:300px;">
                                    <option value="4">SP 1</option>
                                    <option value="5">SP 2</option>
                                    <option value="6">SP 3</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div id="dlg2-buttons">
                        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser2()" style="width:90px">Save</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg2').dialog('close')" style="width:90px">Cancel</a>
                    </div>
                </div>
                <!-- /.tab-pane -->
                <div class="tab-pane" id="tab_4">
                    <div id="contractStep1">
                         <form action="{{route('invoice.manualbodyemail')}}" method="post">
                            <div class="form-group">
                                <label>Body Email</label>
                                <textarea class="textarea" name="spemailcontent" class="form-control" style="width: 100%;" id="ctbodysp">{{$bodyemail}}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
                <div class="tab-pane" id="tab_2">
                    <div id="contractStep1">
                         <form action="{{route('invoice.updatemanualreminder')}}" method="post">
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="judul" class="form-control" value="{{$manual->subject}}">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="textarea" name="spcontent" class="form-control" style="width: 100%;" id="ctsp">{{$manual->content}}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
                <div class="tab-pane" id="tab_3">
                    <div id="contractStep2">
                         <form action="{{route('invoice.updatemanualreminder2')}}" method="post">
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="judul" class="form-control" value="{{$manual2->subject}}">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="textarea" name="spcontent" class="form-control" style="width: 100%;" id="ctsp">{{$manual2->content}}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
                <div class="tab-pane" id="tab_5">
                    <div id="contractStep2">
                         <form action="{{route('invoice.updatemanualreminder3')}}" method="post">
                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="judul" class="form-control" value="{{$manual3->subject}}">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea class="textarea" name="spcontent" class="form-control" style="width: 100%;" id="ctsp">{{$manual3->content}}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- content -->
    </div>
</div>

<div id="editFooterModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:900px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Tambah Manual Invoice</h4>
            </div>
            <div class="modal-body" id="editFooterModalContent">
                <form method="POST" id="formEditFooter">
                     <input type="hidden" name="id" value="">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Manual Invoice</label>
                                  <div id="manual_">
                                    <textarea class="textarea" name="manual_invoice" class="form-control" style="width: 100%;" rows="5" placeholder="NOMOR INVOICE|JENIS INVOICE|NOMINAL"></textarea>
                                  </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 col-sm-offset-9">
                            <button class="btn btn-info pull-right">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
<script type="text/javascript" src="{{asset('plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<script src="{{asset('js/jeasycrud.js')}}"></script>

<script type="text/javascript">
    var entity = "Reminder";
    var get_url = "{{route('invoice.getreminder')}}";
    var insert_url = "{{route('invoice.newreminder')}}";

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


    $(".textarea").wysihtml5();

    $(document).delegate('.getDetail','click',function(){
        $('#detailModalContent').html('<center><img src="{{ asset('img/loading.gif') }}"><p>Loading ...</p></center>');
        var id = $(this).data('id');
        $.post('{{route('payment.getdetail')}}',{id:id}, function(data){
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

    function sendRmd(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to send this '+ids.length+' Reminder ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('invoice.sendreminder')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        $('#dg').datagrid('reload');
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

    function deleteRmd(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to delete this '+ids.length+' Reminder ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('invoice.reminderdelete')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        $('#dg').datagrid('reload');
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

    function postRmd(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to post this '+ids.length+' Reminder ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('invoice.postingdelete')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        $('#dg').datagrid('reload');
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

    function unpostRmd(){
        var ids = [];
        $('input[name=check]:checked').each(function() {
           ids.push($(this).val());
        });
        if(ids.length > 0){
          $.messager.confirm('Confirm','Are you sure you want to unpost this '+ids.length+' Reminder ?',function(r){
              if (r){
                  $('.loadingScreen').show();
                  $.post('{{route('invoice.unpostingdelete')}}',{id:ids}, function(data){
                      alert(data.message);
                      $('.loadingScreen').hide();
                      if(data.success == 1){
                        $('#dg').datagrid('reload');
                      } else{
                        return false;
                      }
                  });
              }
          });
        }
    }

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

    $(document).delegate('.numeric','keypress', function(event) {
        var $this = $(this);
        if ((event.which != 46 || $this.val().indexOf('.') != -1) &&
           ((event.which < 48 || event.which > 57) &&
           (event.which != 0 && event.which != 8))) {
               event.preventDefault();
        }

        var text = $(this).val();
        if ((event.which == 46) && (text.indexOf('.') == -1)) {
            setTimeout(function() {
                if ($this.val().substring($this.val().indexOf('.')).length > 3) {
                    $this.val($this.val().substring(0, $this.val().indexOf('.') + 3));
                }
            }, 1);
        }

        if ((text.indexOf('.') != -1) &&
            (text.substring(text.indexOf('.')).length > 2) &&
            (event.which != 0 && event.which != 8) &&
            ($(this)[0].selectionStart >= text.length - 2)) {
                event.preventDefault();
        }
    });

    function editFooter(){
        var row = $('#dg').datagrid('getSelected');
        $('#manual_').html('<textarea class="textarea" name="manual_invoice" class="form-control" style="width: 100%;" rows="5" placeholder="NOMOR INVOICE|JENIS INVOICE|NOMINAL"></textarea>');
        $.post('{{route('invoice.ajaxgetmanualinv')}}', {id: row.id}, function(data){
            if(data.errMsg){
                $.messager.alert('Warning',data.errMsg);
            }else{
                $('#formEditFooter input[name=id]').val(row.id);
                $('#formEditFooter textarea[name=manual_invoice]').val(data.result.manual_inv);
                $('#editFooterModal').modal("show");
            }
        }, 'json');
    }

    $('#formEditFooter').submit(function(e){
        e.preventDefault();
        $.post('{{route('invoice.ajaxstoremanualinv')}}',$(this).serialize(),function(data){
            if(data.errMsg) $.messager.alert('Warning',data.errMsg);
            if(data.success){
                $.messager.alert('Success','Update success');
                $('#editFooterModal').modal("hide");
                $('#dg').datagrid('reload');
            }
        },'json');
    });
</script>
@endsection
