@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Assets List
@endsection

<!-- page title -->
@section('contentheader_title')
   Assets List
@endsection

<!-- tambahan script atas -->
@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">

    <!-- datepicker -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datepicker/datepicker3.css') }}">
@endsection

@section('contentheader_breadcrumbs')
  <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Assets List</li>
    </ol>
@stop

@section('main-content')
<div class="nav-tabs-custom">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_1" data-toggle="tab">List Harta</a></li>
        <li ><a href="{{route('fixed_asset.type.index')}}">Kelompok Harta</a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="tab_1">

    <div class="row">
      <div class="col-md-11" style="min-height: 300px">
              <!-- content -->

                <!-- template tabel -->
              <table id="dg" title="Master Assets {{date('Y')}}" class="easyui-datagrid" style="width:100%;height:100%" toolbar="#toolbar">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="name" width="120" sortable="true">Nama Aset</th>
                            <th field="date" width="140" sortable="true">Tgl Perolehan</th>
                            <th field="price" width="120" sortable="true">Harga Perolehan</th>
                            <th field="depreciation_type" width="120" sortable="true">Jenis penyusutan</th>
                            <th field="jenis_harta" width="120" sortable="true">Jenis harta</th>
                            <th field="kelompok_harta" width="120" sortable="true">Kelompok harta</th>
                            <th field="masa_manfaat" width="120" sortable="true">Masa manfaat</th>
                            <th field="nilai_sisa" width="150">Nilai sisa buku</th>
                            <th field="per_month" width="150">Penyusutan per Bulan</th>
                            <th field="per_year" width="150">Penyusutan tahun ini</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->

                <!-- icon2 atas table -->
                <div id="toolbar">
                    <a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" id="addNew">New</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="edit()">Edit</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroy()">Remove</a>
                </div>
                <!-- end icon -->

              <!-- content -->
          </div>
    </div>

        </div>
    </div>

<!-- Modal add harta -->
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
<!-- datepicker -->
<script type="text/javascript" src="{{ asset('plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script type="text/javascript">
        var entity = "Master Asset"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('fixed_asset.get')}}";

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

            $('#addNew').click(function(){
                $('#FormModal').modal('show');
                $.post('{{route('fixed_asset.modal.add')}}',{}, function(data){
                    $('#FormModal').find('.modal-content').html(data);
                })
            });
        });

        function edit(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.edit')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }

        function destroy(){
            var row = $('#dg').datagrid('getSelected');
            if(confirm('Apa anda yakin ingin menghapus Harta ini ?')){
                $.post('{{route('fixed_asset.delete')}}',{id: row.id}, function(data){
                        alert('Delete Success');
                        location.reload();
                    })
            }
        }
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
