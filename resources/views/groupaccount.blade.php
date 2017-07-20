@extends('layouts.app')

@section('htmlheader_title')
    Group Account
@endsection

@section('contentheader_title')
   Master Group Account
@endsection

@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Master Group Account</li>
    </ol>
@stop

@section('main-content')
<div class="row">
    <div class="col-md-12">
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
        <table id="dg" title="Group Account" class="easyui-datagrid" style="width:100%;height:400px" toolbar="#toolbar">
            <thead>
                <tr>
                    <th field="grpaccn_name" width="50" sortable="true">Group Account Name</th>
                    <th field="action" width="20" sortable="true">Action</th>
                </tr>
            </thead>
        </table>
        <div id="toolbar">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
        </div>
        <div id="dlg" class="easyui-dialog" style="width:60%" closed="true" buttons="#dlg-buttons">
            <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Information</div>
                <div style="margin-bottom:10px">
                    <input name="grpaccn_name" class="easyui-textbox" required="true" label="Group Account Name:" style="width:100%">
                </div>
            </form>
        </div>
        <div id="dlg-buttons">
            <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box" id="hiddenPanel">
            <div class="box-header with-border">
                <h3 class="box-title"></h3>
            </div>
            <div class="box-body">
                <form method="POST" id="formGroupAcc" action="{{route('groupaccount.updatedetail')}}">
                    <input name="_token" value="{{csrf_token()}}" type="hidden">
                    <input name="id" value="" type="hidden">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label>Choose COA</label>
                                <div class="input-group input-group-md">
                                    <select class="js-example-basic-single" id="selectAccount" style="width:100%">
                                      <option value="">Choose Account</option>
                                      @foreach($accounts as $key => $coa)
                                          <option value="{{$coa->coa_code}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                                      @endforeach
                                    </select>
                                    <span class="input-group-btn">
                                      <button type="button" id="addAccount" class="btn btn-default">ADD</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered" id="tableDetail">
                        <thead>
                            <tr>
                              <th>COA CODE</th>
                              <th>COA NAME</th>
                              <th style="width: 40px">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <button class="btn btn-success pull-right" style="margin-top:30px">Save</button>
                </form>
            </div>
            <div class="box-footer clearfix"></div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/select2/select2.full.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
var entity = "User";
var get_url = "{{route('groupaccount.get')}}";
var get_url2 = "{{route('groupaccount.getdetail')}}";
var insert_url = "{{route('groupaccount.insert')}}";
var update_url = "{{route('groupaccount.update')}}";
var delete_url = "{{route('groupaccount.delete')}}";
$(function(){
    var dg = $('#dg').datagrid({
        url: get_url,
        pagination: true,
        remoteFilter: true, //utk jalanin search filter
        rownumbers: true,
        singleSelect: true,
        fitColumns: true,
        pageSize:100,
        pageList: [100,500,1000],
    });
    dg.datagrid('enableFilter');

    $('#hiddenPanel').hide();

    $(document).delegate('.editGroup','click', function(e){
        $('#hiddenPanel').show();
        var id = $(this).data('id');
        $('.box-title').text($(this).data('name'));
        $('input[name=id]').val(id);
        $('#tableDetail tbody').html('');
        $.get(get_url2, {id:id, raw:1}, function(result){
            if(result.length > 0){
                $.each(result, function(i, item){
                    $('#tableDetail tbody').append('<tr><td><input type="hidden" name="coa_code[]" value="'+item.coa_code+'">'+item.coa_code+'</td><td>'+item.coa_name+'</td><td><a class="removeCoa"><i class="fa fa-times"></i></a></td></tr>');
                });
            }else{
                $('#tableDetail tbody').append('<tr id="rowEmpty"><td colspan="3" class="text-center">No COA</td></tr>');
            }
        }, 'json');
    });

    $(document).delegate('.removeCoa','click', function(e){
        if(confirm('Are you sure want to delete this account ?')){
            $(this).parents('tr').remove();
            if($('#tableDetail tbody tr').length < 1){
                $('#tableDetail tbody').append('<tr id="rowEmpty"><td colspan="3" class="text-center">No COA</td></tr>');
            }
        }
    });

    $(".js-example-basic-single").select2();
    
    var coacode, coaname;
    $("#addAccount").click(function(){
          coacode = $('#selectAccount option:selected').val();
          if(coacode != ""){
            $('#rowEmpty').hide();
            coaname = $('#selectAccount option:selected').data('name');
            $('#tableDetail tbody').append('<tr><td><input type="hidden" name="coa_code[]" value="'+coacode+'">'+coacode+'</td><td>'+coaname+'</td><td><a class="removeCoa"><i class="fa fa-times"></i></a></td></tr>');
          }
     });
});
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection