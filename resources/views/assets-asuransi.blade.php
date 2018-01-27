<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Asuransi - {{$asset->name}}</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
  <div class="row">
      <div class="col-sm-12">
        <button id="newAsuransi">Create New</button>
      </div>
  </div>

  <form method="POST" id="formAsuransi">
    <input type="hidden" id="ptype" name="type" value="new">
    <input type="hidden" id="assetId" name="asset_id" value="{{$asset->id}}">
    <input type="hidden" id="pid" name="id" value="">
    <div class="row">
        <div class="col-sm-12 text-center" style="margin-bottom: 20px;">
            <h4><strong>Data Asuransi</strong></h4>
        </div>
        <!-- UNIT -->
        <div class="col-sm-6">
            <div class="form-group">
                <label>No Polis</label>
                <input type="text" class="form-control" name="polis_no" placeholder="No Polis">
            </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Perusahaan Asuransi</label>
              <input type="text" class="form-control" name="company" placeholder="Perusahaan Asuransi">
          </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Awal</label>
                <input type="text" class="form-control datepicker" name="start_date" placeholder="Tanggal Awal" data-date-format="yyyy-mm-dd">
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Akhir</label>
                <input type="text" class="form-control datepicker" name="end_date" placeholder="Tanggal Akhir" data-date-format="yyyy-mm-dd">
            </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Jumlah Pertanggungan</label>
              <input type="text" class="form-control" name="contribution_value" placeholder="Pertanggungan">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Biaya Asuransi</label>
              <input type="text" class="form-control" name="premi" placeholder="Biaya Asuransi">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>No Ref</label>
              <input type="text" class="form-control" name="ref_no" placeholder="No Referensi">
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
              <label>Keterangan</label>
              <textarea class="form-control" name="note" placeholder="Keterangan"></textarea>
          </div>
        </div>

        <div class="col-sm-12">
            <button class="btn btn-info pull-right">Submit</button>
            <button id="back" type="button" class="btn btn-danger pull-right" style="margin-right: 10px">Back</button>
            <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close Modal</button>
        </div>
    </div>
  </form>

  <table class="table table-striped">
    <thead>
        <tr>
            <th>No Polis</th>
            <th>Perusahaan Asuransi</th>
            <th>Tgl Awal</th>
            <th>Tgl Akhir</th>
            <th>Pertanggungan</th>
            <th>Biaya Asuransi</th>
            <th>No Ref</th>
            <th>Keterangan</th>
        </tr>

    </thead>
    <tbody>
        @if($asset->asuransi->count() > 0)
        @foreach($asset->asuransi as $asuransi)
        <tr>
          <td>{{$asuransi->polis_no}}</td>
          <td>{{$asuransi->company}}</td>
          <td>{{date('d/m/Y',strtotime($asuransi->start_date))}}</td>
          <td>{{date('d/m/Y',strtotime($asuransi->end_date))}}</td>
          <td>IDR {{number_format($asuransi->contribution_value,0)}}</td>
          <td>IDR {{number_format($asuransi->premi,0)}}</td>
          <td>{{$asuransi->ref_no}}</td>
          <td>{{$asuransi->note}}</td>
           <td>
              <a href="#" class="editForm" data-id="{{$asuransi->id}}"><i class="fa fa-pencil"></i>&nbsp;Edit</a><br>
              <a href="#" class="deleteForm" data-id="{{$asuransi->id}}"><i class="fa fa-times"></i>&nbsp;Delete</a>
          </td>
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="7">
            <center>Data asuransi kosong</center>
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
  $('#formAsuransi').hide();

  $(function(){
    $('#newAsuransi').click(function(){
      $('#mainTable').hide();
      $('#formAsuransi').show();
      $('#ptype').val('new');
    });

    $('.editForm').click(function(){
      $('#mainTable').hide();
      $('#formAsuransi').trigger('reset').show();
      $('#ptype').val('edit');
      $('#pid').val($(this).data('id'));
      $.post('{{route('fixed_asset.modal.asuransi.get')}}', {id:$(this).data('id')}, function(data){
        console.log(data);
        $('#formAsuransi').find('input[name=start_date]').val(data.start_date);
        $('#formAsuransi').find('input[name=end_date]').val(data.end_date);
        $('#formAsuransi').find('input[name=polis_no]').val(data.polis_no);
        $('#formAsuransi').find('textarea[name=note]').val(data.note);
        $('#formAsuransi').find('input[name=company]').val(data.company);
        $('#formAsuransi').find('input[name=contribution_value]').val(data.contribution_value);
        $('#formAsuransi').find('input[name=premi]').val(data.premi);
        $('#formAsuransi').find('input[name=ref_no]').val(data.ref_no);
      });
    });

    $('.deleteForm').click(function(){
      if(confirm('Are you sure want to delete this data ?'))
      {
          $.post('{{route('fixed_asset.modal.asuransi.delete')}}', {id:$(this).data('id')}, function(data){
              console.log(data);
              if(data.success){
                alert('Delete Success');
                asuransi();
              }
          });
      }
    });

    $('#back').click(function(){
      $('#mainTable').show();
      $('#formAsuransi').hide();
    });

    $('#formAsuransi').submit(function(e){
      e.preventDefault();
      var cprice = $(this).find('input[name=contribution_value]').val();
      var pprice = $(this).find('input[name=premi]').val();
      if(cprice!=null){
        // validate price
        var reg = /^[1-9]\d*(\.\d+)?$/;
        if(!reg.test(cprice)){
          alert('Format pertanggungan salah, harus berupa angka dan pemisah desimal memakai titik');
          return false;
        }
        if(!reg.test(pprice)){
          alert('Format biaya asuransi salah, harus berupa angka dan pemisah desimal memakai titik');
          return false;
        }
      }

      if($('#ptype').val() == 'new'){
        // submit form
        $.post('{{route('fixed_asset.modal.asuransi.insert')}}', $(this).serialize(), function(data){
            console.log(data);
            if(data.success){
              alert('Insert Success');
              asuransi();
            }
        });
      }else{
        $.post('{{route('fixed_asset.modal.asuransi.update')}}', $(this).serialize(), function(data){
            console.log(data);
            if(data.success){
              alert('Update Success');
              asuransi();
            }
        });
      }

    });

  });
</script>

