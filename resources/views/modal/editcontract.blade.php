
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
            <td>Cost Item</td>
            <td>Edit Details</td>
            <td></td>
          </tr>
          @foreach($costdetail as $cdt)
          <tr class="text-center">
            <input type="hidden" name="contr_id[]" value="{{$id}}">
            <input type="hidden" name="costd_id[]" value="{{$cdt->id}}">
            <td>{{$cdt->cost_name}} ({{$cdt->cost_code}})</td>
            <td>
              <strong>Name :</strong> {{$cdt->costd_name}}<br>
              <strong>Unit :</strong> {{$cdt->costd_unit}}<br>
              <strong>Cost Rate :</strong> {{$cdt->costd_rate}}<br>
              <strong>Cost Burden :</strong> {{$cdt->costd_burden}}<br>
              <strong>Cost Admin :</strong> {{$cdt->costd_admin}}<br>
              <strong>Use Meter :</strong> @if($cdt->costd_ismeter){{'yes'}}@else{{'no'}}@endif<br>
              
              <strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">
                @foreach($invoice_types as $invtype)
                <option value="{{$invtype->invtp_code}}" @if($cdt->invtp_code == $invtype->invtp_code){{'selected="selected"'}}@endif>{{$invtype->invtp_name}}</option>
                @endforeach
              </select>
            </td>
            <td>
              <a href="#" class="removeCost">
                <i class="fa fa-times text-danger"></i>
              </a>
            </td>
          </tr>
          @endforeach
      </table>
      <center><button type="submit">Submit</button></center>
    </form>
              </div>
              



    <script>
    var invoiceTypes = '{!!$inv_types_options!!}';
    var contractID = '{!!$id!!}';
    $('#clickCostItemEdit').click(function(){

            costItem = $('#selectCostItemEdit').val();
            costItemName = $('#selectCostItemEdit option:selected').text();
            $.post('{{route('cost_item.getDetail')}}', {id: costItem}, function(result){
                $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="costd_id[]" value="'+result.id+'"><td>'+costItemName+'</td><td><strong>Name :</strong> '+result.costd_name+'<br><strong>Unit :</strong> '+result.costd_unit+'<br><strong>Cost Rate :</strong> '+result.costd_rate+'<br><strong>Cost Burden :</strong> '+result.costd_burden+'<br><strong>Cost Admin :</strong> '+result.costd_admin+'<br><strong>Use Meter :</strong> '+result.costd_ismeter+'<br><strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');              
            });
            // $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><input type="hidden" name="cost_id[]" value="'+costItem+'"><td>'+costItemName+'</td><td><strong>Name :</strong> <input type="text" name="costd_name[]" class="form-control costd_name"  required><strong>Unit :</strong> <input type="text" name="costd_unit[]" class="form-control costd_unit" required><strong>Cost Rate :</strong> <input type="text" name="costd_rate[]" class="form-control costd_rate" required><strong>Cost Burden :</strong> <input type="text" name="costd_burden[]" class="form-control costd_burden" required><strong>Cost Admin :</strong> <input type="text" name="costd_admin[]" class="form-control costd_admin" required><strong>Invoice Type :</strong> <select name="inv_type[]" class="form-control">'+invoiceTypes+'</select><strong>Use Meter :</strong> <select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    });

    // $('#clickManualCostItemEdit').click(function(){
    //     $('#editTableCost').append('<tr class="text-center"><input type="hidden" name="contr_id[]" value="'+contractID+'"><td><input type="text" name="cost_name[]" class="form-control" placeholder="Cost Item Name" required><br><input type="text" name="cost_code[]" class="form-control" placeholder="Cost Item Code" required></td><td><input type="text" name="costd_name[]" class="form-control costd_name" placeholder="Cost Detail Name" required><br><input type="text" name="costd_unit[]" class="form-control costd_unit"  placeholder="Unit" required><br><input type="text" name="costd_rate[]" placeholder="Rate" class="form-control costd_rate" required><br><input type="text" name="costd_burden[]" placeholder="Abonemen" class="form-control costd_burden" required><br><input type="text" name="costd_admin[]" placeholder="Biaya Admin" class="form-control costd_admin" required><br><select name="is_meter[]" class="form-control"><option value="1">yes</option><option value="0">no</option></select><br><select name="inv_type_custom[]" class="form-control">'+invoiceTypes+'</select></td><td><a href="#" class="removeCost"><i class="fa fa-times text-danger"></i></a></td></tr>');
    // });


    $('#formEditCostItem').submit(function(e){
        e.preventDefault();
        if(!$(this).serialize()){
            alert('Please fill the Cost Item first');
        }else{
            var data = $(this).serialize();
            // console.log(data);
            $.post('{{route('contract.cdtupdate')}}',data, function(result){
                alert(result.message);
                if(result.status == 1) location.reload();
            });
        }
    });


        
    </script>