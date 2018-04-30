<form method="POST" id="formEditMeter">
    <input type="hidden" name="budget_id" value="{{$prd}}">
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_1a" data-toggle="tab">Budget</a></li>
            <li><a href="{{ url('budget/downloadExcel/'.$prd) }}"><i class="fa fa-download"></i> Download</a></li>
            <li><a href="#" data-toggle="modal" data-target="#myModal"><i class="fa fa-upload"></i> Upload</a></li>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_1a">
                <div class="table-responsive">
                    <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
                        <tr>
                            <th>COA</th>
                            <th style="min-width: 250px;">COA Name</th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>Mar</th>
                            <th>Apr</th>
                            <th>May</th>
                            <th>Jun</th>
                            <th>Jul</th>
                            <th>Aug</th>
                            <th>Sep</th>
                            <th>Okt</th>
                            <th>Nov</th>
                            <th>Des</th>
                        </tr>
                        @foreach($budget as $cdt)
                        <tr>
                            <input type="hidden" name="tr_dbudget_id[]" value="{{$cdt->id}}">
                            <td>{{$cdt->coa_code}}</td>
                            <td>{{$cdt->coa_name}}</td>
                            <td>
                                <input type="text" name="jan[]" class="numeric" value="{{$cdt->jan}}">
                            </td>
                            <td>
                                <input type="text" name="feb[]" class="numeric" value="{{$cdt->feb}}">
                            </td>
                            <td>
                                <input type="text" name="mar[]" class="numeric" value="{{$cdt->mar}}">
                            </td>
                            <td>
                                <input type="text" name="apr[]" class="numeric" value="{{$cdt->apr}}">
                            </td>
                            <td>
                                <input type="text" name="may[]" class="numeric" value="{{$cdt->may}}">
                            </td>
                            <td>
                                <input type="text" name="jun[]" class="numeric" value="{{$cdt->jun}}">
                            </td>
                            <td>
                                <input type="text" name="jul[]" class="numeric" value="{{$cdt->jul}}">
                            </td>
                            <td>
                                <input type="text" name="aug[]" class="numeric" value="{{$cdt->aug}}">
                            </td>
                            <td>
                                <input type="text" name="sep[]" class="numeric" value="{{$cdt->sep}}">
                            </td>
                            <td>
                                <input type="text" name="okt[]" class="numeric" value="{{$cdt->okt}}">
                            </td>
                            <td>
                                <input type="text" name="nov[]" class="numeric" value="{{$cdt->nov}}">
                            </td>
                            <td>
                                <input type="text" name="des[]" class="numeric" value="{{$cdt->des}}">
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
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
                <form action="{{route('budget.importExcel')}}" class="form-horizontal" method="post" enctype="multipart/form-data">
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
            <div class="modal-footer"></div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">

$('#formEditMeter').submit(function(e){
    e.preventDefault();
    
    var data = $(this).serialize();
    $.post('{{route('budget.cdtupdate')}}',data, function(result){
        alert(result.message);
        if(result.status == 1) location.reload();
    }); 
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
