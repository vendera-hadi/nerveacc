@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    COA
@endsection

<!-- page title -->
@section('contentheader_title')
   Master COA
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Master COA</li>
    </ol>
@stop

@section('main-content')
<div class="row">
	<div class="col-md-12">
  		<!-- content -->

    @if (Session::has('error'))
    <div class="alert alert-error">{{ Session::get('error') }}</div>
    @endif

    @if (Session::has('success'))
    <div class="alert alert-success">{{ Session::get('success') }}</div>
    @endif

        <!-- template tabel -->
  		<table id="dg" title="Master COA" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
            <!-- kolom -->
            <thead>
                <tr>
                    <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                    <th field="coa_year" width="50" sortable="true">COA Year</th>
                    <th field="coa_code" width="50" sortable="true">COA Code</th>
                    <th field="coa_name" width="50" sortable="true">COA Name</th>
                    <th field="coa_isparent" width="50" sortable="true">COA Parent</th>
                    <th field="coa_level" width="50" sortable="true">COA Level</th>
                    <th field="coa_type" width="50" sortable="true">COA Type</th>
                    <th field="coa_beginning" width="50" sortable="true">COA Beginning</th>
                    <th field="coa_debit" width="50" sortable="true">COA Debit</th>
                    <th field="coa_credit" width="50" sortable="true">COA Credit</th>
                    <th field="coa_ending" width="50" sortable="true">COA Ending</th>
                </tr>
            </thead>
        </table>
        <!-- end table -->

        <!-- icon2 atas table -->
        <div id="toolbar">
            @if(Session::get('role')==1 || in_array(12,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New</a>
            @endif
            @if(Session::get('role')==1 || in_array(13,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit</a>
            @endif
            @if(Session::get('role')==1 || in_array(14,Session::get('permissions')))
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove</a>
            @endif
            <a href="{{ url('coa/downloadCoaExcel') }}" class="easyui-linkbutton" iconCls="icon-save" plain="true">Excel</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" plain="true" id="print">Print</a>
            <a href="javascript:void(0)" onclick="modalUpload()" class="easyui-linkbutton" iconCls="icon-add" plain="true" id="print">Upload COA via Excel</a>
        </div>
        <!-- end icon -->

        <!-- hidden form buat create edit -->
        <div id="dlg" class="easyui-dialog" style="width:60%"
                closed="true" buttons="#dlg-buttons">
            <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">Input Data</div>
                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" name="coa_year" label="COA Year:" style="width:300px;">
                        @for($i=2016; $i<=date('Y'); $i++)
                        <option value="{{$i}}">{{$i}}</option>
                        @endfor
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <input name="coa_code" class="easyui-textbox" label="COA Code:" style="width:100%" data-options="required:true,validType:'length[0,5]'">
                </div>
                <div style="margin-bottom:10px">
                    <input name="coa_name" class="easyui-textbox" label="COA Name:" style="width:100%" data-options="required:true,validType:'length[0,100]'">
                </div>
                <!-- <div style="margin-bottom:10px">
                    <select id="cc" class="easyui-combobox" name="coa_isparent" label="Active:" style="width:300px;">
                        <option value="true" selected>yes</option>
                        <option value="false">no</option>
                    </select>
                </div> -->
                <div style="margin-bottom:10px">
                    <input name="coa_level" class="easyui-textbox" label="COA Level:" style="width:100%" data-options="required:true,numeric:true">
                </div>
                <div style="margin-bottom:10px">
                    <select id="cc" class="easyui-combobox" name="coa_isparent" label="Parent:" style="width:300px;">
                        <option value="true" selected>yes</option>
                        <option value="false">no</option>
                    </select>
                </div>
                <div style="margin-bottom:10px">
                    <select class="easyui-combobox" name="coa_type" label="COA Type:" style="width:300px;">
                        <option value="DEBET" selected>DEBET</option>
                        <option value="KREDIT">KREDIT</option>
                    </select>
                </div>
            </form>
        </div>
        <div id="dlg-buttons">
            <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Save</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')" style="width:90px">Cancel</a>
        </div>
        <!-- end form -->

  		<!-- content -->
	</div>
</div>

<!-- Modal extra -->
<div id="uploadExcelModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:900px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Upload Excel</h4>
            </div>
            <div class="modal-body" id="addUnitModalContent" style="padding: 20px 40px">
                <!-- isi form -->
                <form method="POST" id="uploadExcelForm" action="{{route('coa.upload')}}" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
                            <h4>
                                <strong>Download Excel Template</strong><br><br>
                                <a href="{{url('coa/tplUploadCoaExcel')}}" class="btn btn-warning">Download Excel</a>
                            </h4>
                        </div>
                        <!-- UNIT -->
                        <div class="col-sm-6 col-sm-offset-3">
                            <div class="form-group">
                                <label>Upload template yg sudah diisi</label><br>
                                <input type="file" class="form-control" name="file" placeholder="Browse File" required><br>
                                <small class="text-danger">* Perlu diperhatikan : data coa lama, group account dan format report akan terhapus semua dan akan di replace dgn coa yg diupload</small>
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <button class="btn btn-info pull-right">Submit</button>
                            <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </form>
                <!-- end form -->
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
    var entity = "Master COA"; // nama si tabel, ditampilin di dialog
    var get_url = "{{route('coa.get')}}";
    var insert_url = "{{route('coa.insert')}}";
    var update_url = "{{route('coa.update')}}";
    var delete_url = "{{route('coa.delete')}}";

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
    });

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

    $('#print').click(function(){
        var url = '{!! route('coa.printCoa') !!}';
        var title = 'PRINT COA';
        var w = 640;
        var h = 660;

        openWindow(url, title, w, h);
        return false;
    });

    function modalUpload(){
        $('#uploadExcelModal').modal("show");
    }
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
