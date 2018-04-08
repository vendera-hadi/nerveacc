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
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/select2/select2.min.css') }}">
@endsection

@section('contentheader_breadcrumbs')
  <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Assets List</li>
    </ol>
@stop

@section('main-content')

@if(Session::get('success'))
    <div class="alert alert-success">
      <strong>Success</strong> {{ Session::get('success') }}
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab">List Harta</a></li>
                <li><a href="{{route('fixed_asset.type.index')}}">Kelompok Harta</a></li>
                <li><a href="{{route('fixed_asset.report')}}">Report</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="row">
                        <div class="col-md-11" style="min-height: 300px;">
                            <table id="dg" title="Assets" class="easyui-datagrid" style="width:110%;height:100%" toolbar="#toolbar">
                                <thead>
                                    <tr>
                                        <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                                        <th field="name" width="200" sortable="true">Nama Aset</th>
                                        <th field="date" width="200" sortable="true">Tgl Perolehan</th>
                                        <th field="price" width="200" sortable="true">Harga Perolehan</th>
                                        <!-- <th field="depreciation_type" width="200" sortable="true">Jenis penyusutan</th> -->
                                        <th field="jenis_harta" width="200" sortable="true">Jenis harta</th>
                                        <th field="kelompok_harta" width="200" sortable="true">Kelompok harta</th>
                                        <th field="masa_manfaat" width="200" sortable="true">Masa manfaat</th>
                                        <!-- <th field="nilai_sisa" width="250">Nilai sisa buku<br>Komersial<br>s/d {{date('M y')}}</th>
                                        <th field="per_month" width="250">Peny Komersial<br>per Bln {{date('Y')}}</th>
                                        <th field="per_year" width="250">Peny Komersial<br>per Thn {{date('Y')}}</th>
                                        <th field="nilai_sisa" width="250">Nilai sisa buku<br>Fiskal<br>s/d {{date('M y')}}</th>
                                        <th field="per_month" width="250">Peny Fiskal<br>per Bln {{date('Y')}}</th>
                                        <th field="per_year" width="250">Peny Fiskal<br>per Thn {{date('Y')}}</th> -->
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
                                <a href="javascript:void(0)" class="easyui-linkbutton edit" iconCls="icon-edit" plain="true">Edit</a>
                                @endif
                                @if(Session::get('role')==1 || in_array(83,Session::get('permissions')))
                                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroy()">Remove</a>
                                @endif
                                <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="fiskal()"><i class="fa fa-eye"></i>&nbsp;Kartu Aktiva Fiskal </a>
                                <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="komersial()"><i class="fa fa-eye"></i>&nbsp;Kartu Aktiva Komersial </a>
                                <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="custom()"><i class="fa fa-eye"></i>&nbsp;Kartu Aktiva Custom </a>
                                <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="mutasi()"><i class="fa fa-eye"></i>&nbsp;Mutasi </a>
                                <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="perawatan()"><i class="fa fa-eye"></i>&nbsp;Perawatan </a>
                                <a href="javascript:void(0)" class="easyui-linkbutton" plain="true" onclick="asuransi()"><i class="fa fa-eye"></i>&nbsp;Asuransi </a>
                            </div>
                            <!-- end icon -->
                        </div>
                    </div>
                </div>
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
<!-- select2 -->
<script type="text/javascript" src="{{ asset('plugins/select2/select2.min.js') }}"></script>

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
        });

        var pointerClass;
        @if(Session::get('role')==1 || in_array(81,Session::get('permissions')))
        $('#addNew').click(function(){
            pointerClass = $(this);
            console.log(pointerClass);
            $('#FormModal').modal('show');
            $.post('{{route('fixed_asset.modal.add')}}',{}, function(data){
                $('#FormModal').find('.modal-content').html(data);
            })
        });
        @endif

        @if(Session::get('role')==1 || in_array(82,Session::get('permissions')))
        $('.edit').click(function(){
            pointerClass = $(this);
            console.log(pointerClass);
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.edit')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        });
        @endif

        @if(Session::get('role')==1 || in_array(83,Session::get('permissions')))
        function destroy(){
            var row = $('#dg').datagrid('getSelected');
            if(confirm('Apa anda yakin ingin menghapus Harta ini ?')){
                $.post('{{route('fixed_asset.delete')}}',{id: row.id}, function(data){
                        alert('Delete Success');
                        location.reload();
                    })
            }
        }
        @endif

        function fiskal(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.fiskal')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }

        function komersial(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.komersial')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }

        function custom(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.custom')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }

        function mutasi(){
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.mutasi')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }

        function perawatan() {
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.perawatan')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }

        function asuransi() {
            var row = $('#dg').datagrid('getSelected');
            $.post('{{route('fixed_asset.modal.asuransi')}}',{id: row.id}, function(data){
                    $('#FormModal').modal('show');
                    $('#FormModal').find('.modal-content').html(data);
                })
        }
</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
