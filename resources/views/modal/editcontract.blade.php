<center><h3>Edit Cost Item</h3></center>
<div class="form-group">
    <label>Choose Cost Items</label>
    <select id="selectCostItemEdit" class="form-control" name="costdt[]">
    <?php $tempGroup = ''; ?>
    @foreach($cost_items as $key => $citm)
    @if($citm->cost_name != $tempGroup && $key > 0){!!'</optgroup>'!!}@endif
    @if($citm->cost_name != $tempGroup){!!'<optgroup label="'.$citm->cost_name.' ('.$citm->cost_code.')">'!!}@endif
    <option value="{{$citm->id}}">{{$citm->costd_name}}</option>
    @endforeach
    </select>
</div>
<button type="button" id="clickCostItemEdit">Add Cost Item</button>
<br><br>
    <form method="POST" id="formEditCostItem">
        <table id="editTableCost" width="100%" class="table table-bordered">
            <tr class="text-center">
                <td>Urutan di faktur</td>
                <td>Cost Item</td>
                <td>Edit Details</td>
                <td>&nbsp;</td>
            </tr>
            @foreach($costdetail as $cdt)
            <tr class="text-center">
                <input type="hidden" name="contr_id[]" value="{{$id}}">
                <input type="hidden" name="costd_id[]" class="costdid" value="{{$cdt->id}}">
                <td><input type="number" name="order[]" value="{{$cdt->order}}" style="width: 60px"></td>
                <td>{{$cdt->cost_name}} ({{$cdt->cost_code}})</td>
                <td>
                    <table style="width:100%">
                        <tr>
                            <td style="text-align:right"><strong>Name</strong></td><td width="40">:</td>
                            <td style="text-align:left">{{$cdt->costd_name}}</td>
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Unit</strong></td>
                            <td>:</td><td style="text-align:left">{{$cdt->costd_unit}}</td>
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Cost Rate</strong></td>
                            <td>:</td>
                            <td style="text-align:left">{{$cdt->costd_rate}}</td>
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Cost Burden</strong></td>
                            <td>:</td><td style="text-align:left">{{$cdt->costd_burden}}</td>
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Cost Admin</strong></td>
                            <td>:</td>
                            <td style="text-align:left">{{$cdt->costd_admin}}</td>
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Use Meter</strong></td>
                            <td>:</td>
                            <td style="text-align:left">@if($cdt->costd_ismeter){{'yes'}}@else{{'no'}}@endif</td> 
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Invoice Type</strong></td>
                            <td>:</td>
                            <td>
                                <select name="inv_type[]" class="form-control">
                                @foreach($invoice_types as $invtype)
                                <option value="{{$invtype->id}}" @if($cdt->invtp_id == $invtype->id){!!'selected="selected"'!!}@endif>{{$invtype->invtp_name}}</option>
                                @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align:right"><strong>Billing Period</strong></td><td>:</td>
                            <td>
                                <select name="period[]" class="form-control">
                                    <option value="1" @if($cdt->continv_period == 1){!!'selected="selected"'!!}@endif>1 Month</option>
                                    <option value="2" @if($cdt->continv_period == 2){!!'selected="selected"'!!}@endif>2 Months</option>
                                    <option value="3" @if($cdt->continv_period == 3){!!'selected="selected"'!!}@endif>3 Months</option>
                                    <option value="4" @if($cdt->continv_period == 4){!!'selected="selected"'!!}@endif>4 Months</option>
                                    <option value="6" @if($cdt->continv_period == 5){!!'selected="selected"'!!}@endif>6 Months</option>
                                    <option value="12" @if($cdt->continv_period == 6){!!'selected="selected"'!!}@endif>12 Months</option>
                                </select>
                            </td>
                    </table>
                </td>
                <td>
                    <a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a>
                </td>
            </tr>
            @endforeach
        </table>
        <input type="hidden" name="cont" value="{{$id}}">
        <center><button type="submit">Submit</button></center>
    </form>

<script>
    var invoiceTypes = '{!!$inv_types_options!!}';
    var periods = '<option value="1">1 Month</option><option value="2">2 Months</option><option value="3">3 Months</option><option value="4">4 Months</option><option value="6">6 Months</option><option value="12">12 Months</option></select>';
    var contractID = '{!!$id!!}';
    $('#clickCostItemEdit').click(function(){
        var flag = false;
        costItem = $('#selectCostItemEdit').val();
        $('.costdid').each(function(){
            if($(this).val() == costItem){
                $.messager.alert('Warning', "Cost Item already exist in the list below");
                flag = true;
            }
        });
        if(!flag){
            costItemName = $('#selectCostItemEdit option:selected').parent().attr('label');
            $.post('{{route('cost_item.getDetail')}}', {id: costItem}, function(result){
                $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="costd_id[]" class="costdid" value="'+result.id+'"><td><input type="number" name="order[]" value="0" style="width: 60px"></td><td>'+costItemName+'</td><td><table style="width:100%"><tr><td style="text-align:right"><strong>Name</strong></td><td width="40">:</td><td style="text-align:left">'+result.costd_name+'</td></tr><tr><td style="text-align:right"><strong>Unit</strong></td><td>:</td><td style="text-align:left">'+result.costd_unit+'</td></tr><tr><td style="text-align:right"><strong>Cost Rate</strong></td><td>:</td><td style="text-align:left">'+result.costd_rate+'</td></tr><tr><td style="text-align:right"><strong>Cost Burden</strong></td><td>:</td><td style="text-align:left">'+result.costd_burden+'</td></tr><tr><td style="text-align:right"><strong>Cost Admin</strong></td><td>:</td><td style="text-align:left">'+result.costd_admin+'</td></tr><tr><td style="text-align:right"><strong>Use Meter</strong></td><td>:</td><td style="text-align:left">'+result.costd_ismeter+'</td> </tr><tr><td style="text-align:right"><strong>Invoice Type</strong></td><td>:</td><td><select name="inv_type[]" class="form-control">'+invoiceTypes+'</select></td></tr><tr><td style="text-align:right"><strong>Billing Period</strong></td><td>:</td><td><select name="period[]" class="form-control">'+periods+'</select></td></tr></table></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');

            });
        }
        // $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="cost_id[]" value="'+costItem+'"><td>'+costItemName+'</td><td><strong>Name :</strong> <input type="text" name="costd_name[]" class="form-control costd_name"  required><strong>Unit :</strong> <input type="text" name="costd_unit[]" class="form-control costd_unit" required><strong>Cost Rate :</strong> <input type="text" name="costd_rate[]" class="form-control costd_rate" required><strong>Cost Burden :</strong> <input type="text" name="costd_burden[]" class="form-control costd_burden" required><strong>Cost Admin :</strong> <input type="text" name="costd_admin[]" class="form-control costd_admin" required><strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">'+invoiceTypes+'</select><strong>Use Meter :</strong> <select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    });

    // $('#clickManualCostItemEdit').click(function(){
    //     $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><td><input type="text" name="cost_name[]" class="form-control" placeholder="Cost Item Name" required><br><input type="text" name="cost_code[]" class="form-control" placeholder="Cost Item Code" required></td><td><input type="text" name="costd_name[]" class="form-control costd_name" placeholder="Cost Detail Name" required><br><input type="text" name="costd_unit[]" class="form-control costd_unit"  placeholder="Unit" required><br><input type="text" name="costd_rate[]" placeholder="Rate" class="form-control costd_rate" required><br><input type="text" name="costd_burden[]" placeholder="Abonemen" class="form-control costd_burden" required><br><input type="text" name="costd_admin[]" placeholder="Biaya Admin" class="form-control costd_admin" required><br><select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select><br><select name="inv_type_custom[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    // });


    $('#formEditCostItem').submit(function(e){
        e.preventDefault();
        //BOLEH KOSONG
        /*
        if(!$(this).serialize()){
            alert('Please fill the Cost Item first');
        }else{
        */
        var data = $(this).serialize();
        // console.log(data);
        $.post('{{route('contract.cdtupdate')}}',data, function(result){
            alert(result.message);
            if(result.status == 1) location.reload();
        });
        //}
    });

</script>