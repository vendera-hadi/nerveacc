@if ($st->status === false)
<form method="POST" id="formEditMeter">
  <input type="hidden" name="prd_id" value="{{$prd}}">
<!-- Custom Tabs -->
<div class="nav-tabs-custom">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab_1a" data-toggle="tab">Listrik</a></li>
    <li><a href="#tab_2a" data-toggle="tab">Air</a></li>
    <li class="dropdown bg-success">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        Download Template <span class="caret"></span>
      </a>
      <ul class="dropdown-menu">
        <li role="presentation"><a role="menuitem" tabindex="-1" href="{{ url('period_meter/downloadExcel',[$st->id,1]) }}">Listrik</a></li>
        <li role="presentation" class="divider"></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="{{ url('period_meter/downloadExcel',[$st->id,2]) }}">Air</a></li>
      </ul>
      <li class="bg-info"><a href="#" data-toggle="modal" data-target="#myModal"><i class="fa fa-upload"></i> Upload</a></li>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab_1a">
      <div class="table-responsive">
        <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
            <tr class="text-center">
              <th>No Unit</th>
              <th>Cost</th>
              <th>Daya</th>
              <th>Meter Start</th>
              <th>Meter End</th>
              <th>Meter Used</th>
              <th>Meter Cost</th>
              <th>BPJU 3%</th>
              <th>Total</th>
            </tr>
            @foreach($listrik as $cdt)
            <tr class="text-center">
              <input type="hidden" name="tr_meter_id[]" value="{{$cdt->id}}">
              <input type="hidden" name="meter_start[]" value="{{$cdt->meter_start}}">
              <input type="hidden" name="meter_rate[]" value="{{$cdt->costd_rate}}">
              <input type="hidden" name="meter_burden[]" value="{{$cdt->meter_burden}}">
              <input type="hidden" name="meter_admin[]" value="{{$cdt->meter_admin}}">
              <input type="hidden" name="cost_id[]" value="{{$cdt->cost_id}}">
              <input type="hidden" name="daya[]" value="{{$cdt->daya}}">
              <input type="hidden" name="percentage[]" value="{{empty($cdt->percentage) ? 0 : $cdt->percentage }}">
              <input type="hidden" name="value_type[]" value="{{$cdt->value_type}}">
              <td>{{$cdt->unit_code}}</td>
              <td>{{$cdt->costd_name}}</td>
              <td>{{$cdt->daya}}</td>
              <td>{{$cdt->meter_start}}</td>
              <td>
                <input type="text" name="meter_end[]" class="numeric meter_e" value="{{$cdt->meter_end == 0 ? $cdt->meter_start : $cdt->meter_end}}">
              </td>
              <td>{{$cdt->meter_used}}</td>
              <td>{{number_format($cdt->meter_cost)}}</td>
              <td>{{number_format($cdt->other_cost)}}</td>
              <td>{{number_format($cdt->total)}}</td>
            </tr>
            @endforeach
        </table>
      </div>
    </div>
    <!-- /.tab-pane -->
    <div class="tab-pane" id="tab_2a">
      <div class="table-responsive">
        <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
            <tr class="text-center">
              <th>No Unit</th>
              <th>Cost</th>
              <th>Meter Start</th>
              <th>Meter End</th>
              <th>Meter Used</th>
              <th>Meter Cost</th>
              <th>Biaya Beban Tetap</th>
              <th>Biaya Pemeliharaan Meter</th>
              <th>Total</th>
            </tr>
            @foreach($air as $cds)
            <tr class="text-center">
              <input type="hidden" name="tr_meter_id[]" value="{{$cds->id}}">
              <input type="hidden" name="meter_start[]" value="{{$cds->meter_start}}">
              <input type="hidden" name="meter_rate[]" value="{{$cds->costd_rate}}">
              <input type="hidden" name="meter_burden[]" value="{{$cds->meter_burden}}">
              <input type="hidden" name="meter_admin[]" value="{{$cds->meter_admin}}">
              <input type="hidden" name="cost_id[]" value="{{$cds->cost_id}}">
              <input type="hidden" name="daya[]" value="{{$cds->daya}}">
              <td>{{$cds->unit_code}}</td>
              <td>{{$cds->costd_name}}</td>
              <td>{{$cds->meter_start}}</td>
              <td>
                <input type="text" name="meter_end[]" class="numeric meter_e" value="{{$cds->meter_end == 0 ? $cds->meter_start : $cds->meter_end}}">
              </td>
              <td>{{$cds->meter_used}}</td>
              <td>{{number_format($cds->meter_cost)}}</td>
              <td>{{number_format($cds->costd_burden)}}</td>
              <td>{{number_format($cds->costd_admin)}}</td>
              <td>{{number_format($cds->total)}}</td>
            </tr>
            @endforeach
        </table>
      </div>
    </div>
  </div>
  <!-- /.tab-content -->
</div>
<!-- nav-tabs-custom -->
</div>
<div class="text-left">
  <button type="submit" class="btn btn-sm btn-flat btn-info">Submit</button>
</div>
</form>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Upload Excel</h4>
      </div>
      <div class="modal-body">
        <form action="{{route('period_meter.importExcel')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
          <div class="box-body">
            <div class="form-group">
              <label for="exampleInputFile">File input</label>
                <input type="file" name="import_file" id="exampleInputFile"/>
                <p class="help-block">Upload File Excel Template.</p>
                <input type="hidden" name="prd" value="{{$prd}}">
                <button type="submit" class="btn btn-primary btn-sm">Import File</button>

            </div>
          </div>
      </div>
      <div class="modal-footer">

      </div>
     </form>
    </div>
  </div>
</div>
<script type="text/javascript">

  $('#formEditMeter').submit(function(e){
    e.preventDefault();
    if(!$(this).serialize()){
        alert('Please fill the meter');
    }else{
        var data = $(this).serialize();
        $.post('{{route('period_meter.cdtupdate')}}',data, function(result){
            alert(result.message);
            if(result.status == 1) location.reload();
        });

    }
});

$(".addbtn").click(function(){
$.ajax({
      url:'add-catagory',
      data:{
        data:new FormData($("#upload_form")[0]),
      },
      dataType:'json',
      async:false,
      type:'post',
      processData: false,
      contentType: false,
      success:function(response){
        console.log(response);
      },
    });
 });
</script>

@else
<div class="nav-tabs-custom">
  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab_1a" data-toggle="tab">Electricity</a></li>
    <li><a href="#tab_2a" data-toggle="tab">Water</a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="tab_1a">
      <div class="table-responsive">
        <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
            <tr class="text-center">
              <th>No Unit</th>
              <th>Cost</th>
              <th>Daya</th>
              <th>Meter Start</th>
              <th>Meter End</th>
              <th>Meter Used</th>
              <th>Meter Cost</th>
              <th>BPJU 3%</th>
              <th>Total</th>
            </tr>
            @foreach($listrik as $cdt)
            <tr class="text-center">
              <td>{{$cdt->unit_code}}</td>
              <td>{{$cdt->costd_name}}</td>
              <td>{{$cdt->daya}}</td>
              <td>{{$cdt->meter_start}}</td>
              <td>{{$cdt->meter_end}}</td>
              <td>{{$cdt->meter_used}}</td>
              <td>{{number_format($cdt->meter_cost)}}</td>
              <td>{{number_format($cdt->other_cost)}}</td>
              <td>{{number_format($cdt->total)}}</td>
            </tr>
            @endforeach
        </table>
      </div>
    </div>
    <!-- /.tab-pane -->
    <div class="tab-pane" id="tab_2a">
      <div class="table-responsive">
        <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
            <tr class="text-center">
              <th>No Unit</th>
              <th>Cost</th>
              <th>Meter Start</th>
              <th>Meter End</th>
              <th>Meter Used</th>
              <th>Meter Cost</th>
              <th>Biaya Beban Tetap</th>
              <th>Biaya Pemeliharaan Meter</th>
              <th>Total</th>
            </tr>
            @foreach($air as $cds)
            <tr class="text-center">
              <td>{{$cds->unit_code}}</td>
              <td>{{$cds->costd_name}}</td>
              <td>{{$cds->meter_start}}</td>
              <td>{{$cds->meter_end}}</td>
              <td>{{$cds->meter_used}}</td>
              <td>{{number_format($cds->meter_cost)}}</td>
              <td>{{number_format($cds->costd_burden)}}</td>
              <td>{{number_format($cds->costd_admin)}}</td>
              <td>{{number_format($cds->total)}}</td>
            </tr>
            @endforeach
        </table>
      </div>
    </div>
  </div>
  <!-- /.tab-content -->
</div>
<!-- nav-tabs-custom -->
</div>
@endif