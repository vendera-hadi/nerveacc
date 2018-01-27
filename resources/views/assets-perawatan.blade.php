<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Perawatan - {{$asset->name}}</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
  <div class="row">
      <div class="col-sm-12">
        <button id="newPerawatan">Create New</button>
      </div>
  </div>

  <form method="POST" id="formPerawatan">
    <input type="hidden" id="ptype" name="type" value="new">
    <input type="hidden" id="assetId" name="asset_id" value="{{$asset->id}}">
    <input type="hidden" id="pid" name="id" value="">
    <div class="row">
        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
            <h4><strong>Data Perawatan</strong></h4>
        </div>
        <!-- UNIT -->
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal</label>
                <input type="text" class="form-control datepicker" name="date" placeholder="Tanggal Perawatan" data-date-format="yyyy-mm-dd" required>
            </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>No Referensi</label>
              <input type="text" class="form-control" name="ref_no" placeholder="No Referensi">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Keterangan</label>
              <textarea class="form-control" name="note" placeholder="Keterangan"></textarea>
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Biaya</label>
              <input type="text" class="form-control" name="price" placeholder="Biaya Perawatan">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>No Part</label>
              <input type="text" class="form-control" name="part_no" placeholder="No Part">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Nama Pekerja</label>
              <input type="text" class="form-control" name="user" placeholder="Nama Pekerja">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Supplier</label>
              <input type="text" class="form-control" name="supplier" placeholder="Supplier">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>No Invoice</label>
              <input type="text" class="form-control" name="invoice_no" placeholder="No Invoice">
          </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Akhir Garansi</label>
                <input type="text" class="form-control datepicker" name="guarantee_duedate" placeholder="Tanggal Akhir Garansi" data-date-format="yyyy-mm-dd">
            </div>
        </div>

        <div class="col-sm-12">
            <button class="btn btn-info pull-right">Submit</button>
            <button id="back" type="button" class="btn btn-danger pull-right" style="margin-right: 10px">Back</button>
            <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close Modal</button>
        </div>
    </div>
  </form>


  <table id="mainTable" class="table table-striped">
    <thead>
        <tr>
            <th>Tgl</th>
            <th>No Ref</th>
            <th>Keterangan</th>
            <th>Biaya</th>
            <th>No Part</th>
            <th>Nama Pekerja</th>
            <th>No Invoice</th>
            <th>Tgl Akhir Garansi</th>
            <th>Action</th>
        </tr>

    </thead>
    <tbody>
        @if($asset->perawatan->count() > 0)
        @foreach($asset->perawatan as $perawatan)
        <tr>
          <td>{{date('d/m/Y',strtotime($perawatan->date))}}</td>
          <td>{{$perawatan->ref_no}}</td>
          <td>{{$perawatan->note}}</td>
          <td>IDR {{number_format($perawatan->price,0)}}</td>
          <td>{{$perawatan->part_no}}</td>
          <td>{{$perawatan->user}}</td>
          <td>{{$perawatan->invoice_no}}</td>
          <td>{{date('d/m/Y',strtotime($perawatan->guarantee_duedate))}}</td>
          <td>
              <a href="#" class="editForm" data-id="{{$perawatan->id}}"><i class="fa fa-pencil"></i>&nbsp;Edit</a><br>
              <a href="#" class="deleteForm" data-id="{{$perawatan->id}}"><i class="fa fa-times"></i>&nbsp;Delete</a>
          </td>
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="8">
            <center>Data perawatan kosong</center>
          </td>
        </tr>
        @endif
    </tbody>
  </table>
</div>


<script type="text/javascript">
  $('.datepicker').datepicker({
            autoclose: true
        });
  $('#formPerawatan').hide();

  $(function(){
    $('#newPerawatan').click(function(){
      $('#mainTable').hide();
      $('#formPerawatan').show();
      $('#ptype').val('new');
    });

    $('.editForm').click(function(){
      $('#mainTable').hide();
      $('#formPerawatan').trigger('reset').show();
      $('#ptype').val('edit');
      $('#pid').val($(this).data('id'));
      $.post('{{route('fixed_asset.modal.perawatan.get')}}', {id:$(this).data('id')}, function(data){
        console.log(data);
        $('#formPerawatan').find('input[name=date]').val(data.date);
        $('#formPerawatan').find('input[name=ref_no]').val(data.ref_no);
        $('#formPerawatan').find('textarea[name=note]').val(data.note);
        $('#formPerawatan').find('input[name=price]').val(data.price);
        $('#formPerawatan').find('input[name=part_no]').val(data.part_no);
        $('#formPerawatan').find('input[name=user]').val(data.user);
        $('#formPerawatan').find('input[name=supplier]').val(data.supplier);
        $('#formPerawatan').find('input[name=invoice_no]').val(data.invoice_no);
        $('#formPerawatan').find('input[name=guarantee_duedate]').val(data.guarantee_duedate);
      });
    });

    $('.deleteForm').click(function(){
      if(confirm('Are you sure want to delete this data ?'))
      {
          $.post('{{route('fixed_asset.modal.perawatan.delete')}}', {id:$(this).data('id')}, function(data){
              console.log(data);
              if(data.success){
                alert('Delete Success');
                perawatan();
              }
          });
      }
    });

    $('#back').click(function(){
      $('#mainTable').show();
      $('#formPerawatan').hide();
    });

    $('#formPerawatan').submit(function(e){
      e.preventDefault();
      var price = $(this).find('input[name=price]').val();
      if(price!=null){
        // validate price
        var reg = /^[1-9]\d*(\.\d+)?$/;
        if(!reg.test(price)){
          alert('Format biaya salah, harus berupa angka dan pemisah desimal memakai titik');
          return false;
        }
      }

      if($('#ptype').val() == 'new'){
        // submit form
        $.post('{{route('fixed_asset.modal.perawatan.insert')}}', $(this).serialize(), function(data){
            console.log(data);
            if(data.success){
              alert('Insert Success');
              perawatan();
            }
        });
      }else{
        $.post('{{route('fixed_asset.modal.perawatan.update')}}', $(this).serialize(), function(data){
            console.log(data);
            if(data.success){
              alert('Update Success');
              perawatan();
            }
        });
      }

    });
  });
</script>