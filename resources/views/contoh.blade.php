@extends('layouts.app')

@section('contentheader_title')
   Contoh Page Header
@endsection

@section('htmlheader_title')
	Contoh Title
@endsection

@section('htmlheader_scripts')
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/default/easyui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/icon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/jquery-easyui/themes/color.css') }}">
@endsection

@section('contentheader_breadcrumbs')
	<ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Contoh</li>
    </ol>
@stop

@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			<div class="col-md-11">
          		<!-- content -->

                <!-- template tabel -->
          		<table id="dg" title="DataGrid" style="width:700px;height:250px" data-options="
                singleSelect:true,
                data:data
            ">
                <thead>
                    <tr>
                        <th data-options="field:'itemid',width:80">Item ID</th>
                        <th data-options="field:'productid',width:100">Product</th>
                        <th data-options="field:'listprice',width:80,align:'right'">List Price</th>
                        <th data-options="field:'unitcost',width:80,align:'right'">Unit Cost</th>
                        <th data-options="field:'attr1',width:250">Attribute</th>
                        <th data-options="field:'status',width:60,align:'center'">Status</th>
                    </tr>
                </thead>
            </table>

                <!-- end table -->
                
                <!-- icon2 atas table -->
                <div id="toolbar">
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New User</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit User</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove User</a>
                </div>
                <!-- end icon -->
            
                <!-- hidden form buat create edit -->
                <div id="dlg" class="easyui-dialog" style="width:400px"
                        closed="true" buttons="#dlg-buttons">
                    <form id="fm" method="post" novalidate style="margin:0;padding:20px 50px">
                        <div style="margin-bottom:20px;font-size:14px;border-bottom:1px solid #ccc">User Information</div>
                        <div style="margin-bottom:10px">
                            <input name="firstname" class="easyui-textbox" required="true" label="First Name:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="lastname" class="easyui-textbox" required="true" label="Last Name:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="phone" class="easyui-textbox" required="true" label="Phone:" style="width:100%">
                        </div>
                        <div style="margin-bottom:10px">
                            <input name="email" class="easyui-textbox" required="true" validType="email" label="Email:" style="width:100%">
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
	</div>
@endsection

@section('footer-scripts')
<script src="{{asset('plugins/jQueryUI/jquery-ui.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/jquery.easyui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('plugins/jquery-easyui/datagrid-filter.js') }}"></script>
<script type="text/javascript">
        var entity = "User"; // nama si tabel, ditampilin di dialog
        var insert_url = "{{route('contoh.insert')}}";
        var update_url = "{{route('contoh.update')}}";
        var delete_url = "{{route('contoh.delete')}}";

        var data = [
            {"productid":"FI-SW-01","productname":"Koi","unitcost":10.00,"status":"P","listprice":36.50,"attr1":"Large","itemid":"EST-1"},
            {"productid":"K9-DL-01","productname":"Dalmation","unitcost":12.00,"status":"P","listprice":18.50,"attr1":"Spotted Adult Female","itemid":"EST-10"},
            {"productid":"RP-SN-01","productname":"Rattlesnake","unitcost":12.00,"status":"P","listprice":38.50,"attr1":"Venomless","itemid":"EST-11"},
            {"productid":"RP-SN-01","productname":"Rattlesnake","unitcost":12.00,"status":"P","listprice":26.50,"attr1":"Rattleless","itemid":"EST-12"},
            {"productid":"RP-LI-02","productname":"Iguana","unitcost":12.00,"status":"P","listprice":35.50,"attr1":"Green Adult","itemid":"EST-13"},
            {"productid":"FL-DSH-01","productname":"Manx","unitcost":12.00,"status":"N","listprice":158.50,"attr1":"Tailless","itemid":"EST-14"},
            {"productid":"FL-DSH-01","productname":"Manx","unitcost":12.00,"status":"P","listprice":83.50,"attr1":"With tail","itemid":"EST-15"},
            {"productid":"FL-DLH-02","productname":"Persian","unitcost":12.00,"status":"P","listprice":23.50,"attr1":"Adult Female","itemid":"EST-16"},
            {"productid":"FL-DLH-02","productname":"Persian","unitcost":12.00,"status":"N","listprice":89.50,"attr1":"Adult Male","itemid":"EST-17"},
            {"productid":"AV-CB-01","productname":"Amazon Parrot","unitcost":92.00,"status":"N","listprice":63.50,"attr1":"Adult Male","itemid":"EST-18"}
        ];
        $(function(){
            var dg = $('#dg').datagrid({
                filterBtnIconCls:'icon-filter'
            });
            dg.datagrid('enableFilter', [{
                field:'listprice',
                type:'numberbox',
                options:{precision:1},
                op:['equal','notequal','less','greater']
            },{
                field:'unitcost',
                type:'numberbox',
                options:{precision:1},
                op:['equal','notequal','less','greater']
            },{
                field:'status',
                type:'combobox',
                options:{
                    panelHeight:'auto',
                    data:[{value:'',text:'All'},{value:'P',text:'P'},{value:'N',text:'N'}],
                    onChange:function(value){
                        if (value == ''){
                            dg.datagrid('removeFilterRule', 'status');
                        } else {
                            dg.datagrid('addFilterRule', {
                                field: 'status',
                                op: 'equal',
                                value: value
                            });
                        }
                        dg.datagrid('doFilter');
                    }
                }
            }]);
        });

</script>
<script src="{{asset('js/jeasycrud.js')}}"></script>
@endsection
