<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{$title}} Kelompok Harta</h4>
</div>
<div class="modal-body" style="padding: 20px 40px">
    <!-- isi form -->
    <form method="POST" action="{{$action}}">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Jenis Harta</label>
                    <select class="form-control" name="jenis_harta" placeholder="Jenis Harta" required>
                        <option @if(@$detail && $detail->jenis_harta == 'BUKAN BANGUNAN') selected @endif>BUKAN BANGUNAN</option>
                        <option @if(@$detail && $detail->jenis_harta == 'BANGUNAN') selected @endif>BANGUNAN</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Kelompok Harta</label>
                    <input type="text" class="form-control" name="kelompok_harta" placeholder="Kelompok Harta" value="{{ @$detail ? $detail->kelompok_harta : ''}}" required>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Masa Manfaat<br></label>
                    <input type="number" class="form-control" name="masa_manfaat" min="1" max="20" value="{{ @$detail ? $detail->masa_manfaat : 1 }}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>% Depresiasi Custom<br><small>(jika memakai garis lurus atau saldo menurun abaikan ini)</small></label>
                    <input type="number" class="form-control" name="custom_rule" min="0" max="100" value="{{@$detail ? $detail->custom_rule * 100 : 0}}">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>COA Debit</label>
                    <div class="input-group input-group-md">
                        <select class="js-example-basic-single" name="debit_coa_code" style="width:100%">
                          <option value="">Choose Account</option>
                          @foreach($accounts as $key => $coa)
                              <option value="{{str_replace(" ","", $coa->coa_code)}}" data-name="{{$coa->coa_name}}" >{{$coa->coa_code." ".$coa->coa_name}}</option>
                          @endforeach
                        </select>
                      </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>COA Credit</label>
                    <div class="input-group input-group-md">
                        <select class="js-example-basic-single" name="credit_coa_code" style="width:100%">
                          <option value="">Choose Account</option>
                          @foreach($accounts as $key => $coa)
                              <option value="{{str_replace(" ","", $coa->coa_code)}}" data-name="{{$coa->coa_name}}">{{$coa->coa_code." ".$coa->coa_name}}</option>
                          @endforeach
                        </select>
                      </div>
                </div>
            </div>

            <div class="col-sm-12">
                <button class="btn btn-info pull-right">Submit</button>
                <button type="button" class="btn btn-danger pull-right" style="margin-right: 10px" data-dismiss="modal">Close</button>
            </div>
        </div>
    </form>
    <!-- end form -->
</div>
<script type="text/javascript">
$(function(){
    $(".js-example-basic-single").select2();

    @if(@$detail)
    $("select[name=debit_coa_code]").val("{{str_replace(" ","", @$detail->debit_coa_code)}}").trigger("change");
    $("select[name=credit_coa_code]").val("{{str_replace(" ","", @$detail->credit_coa_code)}}").trigger("change");
    @endif

});
</script>