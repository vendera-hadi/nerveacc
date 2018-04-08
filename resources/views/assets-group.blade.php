@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Asset Types List
@endsection

<!-- page title -->
@section('contentheader_title')
   Asset Types List
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">

    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
  <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Asset Types List</li>
    </ol>
@stop

@section('main-content')
<div class="nav-tabs-custom">
      <ul class="nav nav-tabs">
        <li ><a href="{{route('fixed_asset.index')}}" >List Harta</a></li>
        <li class="active"><a href="#tab_1">Kelompok Harta</a></li>
        <li><a href="{{route('fixed_asset.report')}}">Report</a></li>
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

    <div class="row">
        <div class="col-md-11" style="min-height: 300px">
            <table id="dg" title="Kelompok Harta" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="jenis_harta" width="120" sortable="true">Jenis Harta</th>
                            <th field="kelompok_harta" width="120" sortable="true">Kelompok Harta</th>
                            <th field="masa_manfaat" width="120" sortable="true">Masa Manfaat</th>
                            <th field="garis_lurus" width="120" sortable="true">% Garis Lurus</th>
                            <th field="saldo_menurun" width="120" sortable="true">% Saldo Menurun</th>
                            <th field="custom_rule" width="120" sortable="true">% Jika Custom</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->

                <!-- icon2 atas table -->
                <div id="toolbar">
                    @if(Session::get('role')==1 || in_array(81,Session::get('permissions')))
                    <a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" id="addNew">New</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(82,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="edit()">Edit</a>
                    @endif
                    @if(Session::get('role')==1 || in_array(83,Session::get('permissions')))
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroy()">Remove</a>
                    @endif
                </div>
                <!-- end icon -->
        </div>
    </div>

    </div>
</div>

<!-- Modal add kelompok harta -->
<div id="FormModal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width:900px">
        <div class="modal-content">
        </div>
    </div>
</div>
<!-- End Modal -->

@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>
<script type="text/javascript">
        var entity = "Master Asset"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('fixed_asset.type.get')}}";

        $(function(){

            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                remoteFilter: true, //utk jalanin search filter
                rownumbers: true,
                singleSelect: true,
                fitColumns: true
            });
            dg.datagrid('enableFilter');

            @if(Session::get('role')==1 || in_array(81,Session::get('permissions')))
            $('#addNew').click(function(){
                $('#FormModal').modal('show');
                $.post('{{route('fixed_asset.type.modal.add')}}',{}, function(data){
                    $('#FormModal').find('.modal-content').html(data);
                })
            });
            @endif
        });

        @if(Session::get('role')==1 || in_array(82,Session::get('permissions')))
        function edit(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.type.modal.edit')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }
        @endif

        @if(Session::get('role')==1 || in_array(83,Session::get('permissions')))
        function destroy(){
            var row = $('#dg').datagrid('getSelected');
            if(confirm('Apa anda yakin ingin menghapus kelompok harta ini ?')){
                $.post('{{route('fixed_asset.type.delete')}}',{id: row.id}, function(data){
                        alert('Delete Success');
                        location.reload();
                    })
            }
        }
        @endif
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
