var url;
function newUser(){
    console.log('create');
    $('#dlg').dialog('open').dialog('center').dialog('setTitle','New '+entity);
    $('#fm').form('clear');
    url = insert_url;
}
function editUser(){
    console.log('edit');
    var row = $('#dg').datagrid('getSelected');
    if (row){
        $('#dlg').dialog('open').dialog('center').dialog('setTitle','Edit '+entity);
        $('#fm').form('load',row);
        url = update_url+'?id='+row.id;
    }
}
function saveUser(){
    console.log('save or edit');
    $('#fm').form('submit',{
        url: url,
        onSubmit: function(){
            return $(this).form('validate');
        },
        success: function(result){
            var result = eval('('+result+')');
            if (result.errorMsg){
                $.messager.show({
                    title: 'Error',
                    msg: result.errorMsg
                });
            } else {
                $('#dlg').dialog('close');      // close the dialog
                $('#dg').datagrid('reload');    // reload the user data
            }
        },
        error: function (request, status, error) {
            alert(request.responseText);
          }
    });
}
function destroyUser(){
    console.log('destroy');
    var row = $('#dg').datagrid('getSelected');
    if (row){
        $.messager.confirm('Confirm','Are you sure you want to destroy this '+entity+'?',function(r){
            if (r){
                $.post(delete_url,{id:row.id},function(result){
                    if (result.success){
                        $('#dg').datagrid('reload');    // reload the user data
                    } else {
                        $.messager.show({   // show error message
                            title: 'Error',
                            msg: result.errorMsg
                        });
                    }
                },'json');
            }
        });
    }
}