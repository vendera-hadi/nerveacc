@extends('layouts.app')

<!-- title tab -->
@section('htmlheader_title')
    Faktur
@endsection

<!-- page title -->
@section('contentheader_title')
   Faktur Invoice
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
        <li class="active">Faktur Invoice</li>
    </ol>
@stop

@section('main-content')
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-11">
                <!-- content -->

                <!-- template tabel -->
                <table id="dg" title="Faktur Invoice" class="easyui-datagrid" style="width:100%;height:250%">
                    <!-- kolom -->
                    <thead>
                        <tr>
                            <!-- tambahin sortable="true" di kolom2 yg memungkinkan di sort -->
                            <th field="inv_number" width="50" sortable="true">No. Invoice</th>
                            <th field="tenan_name" width="50" sortable="true">Nama Tenan</th>
                            <th field="inv_date" width="50" sortable="true">Tanggal Faktur</th>
                            <th field="inv_duedate" width="50" sortable="true">Jatuh Tempo Faktur</th>
                            <th field="inv_ppn_amount" width="50" sortable="true">Jumlah pembayaran</th>
                        </tr>
                    </thead>
                </table>
                <!-- end table -->

                <!-- content -->
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/datagrid-filter.js') }}"></script>
<script type="text/javascript">
        var entity = "Faktur Invoice"; // nama si tabel, ditampilin di dialog
        var get_url = "{{route('faktur.get')}}";

        $(function(){
            var dg = $('#dg').datagrid({
                url: get_url,
                pagination: true,
                rownumbers: true,
                singleSelect: true,
                fitColumns: true,
                pageSize:30
            });
            dg.datagrid('enableFilter');
        });
</script>
@endsection
