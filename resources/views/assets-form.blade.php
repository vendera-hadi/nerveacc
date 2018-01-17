<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{$title}} Harta</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
    <!-- isi form -->
    <form method="POST" action="{{$action}}">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Pilih Kelompok Harta</label>
                    <select class="form-control" name="ms_asset_type_id" placeholder="Kelompok Harta" required>
                        <option value="">Pilih Kelompok Harta</option>
                        @foreach($kelompok_harta as $kh)
                        <option value="{{$kh->id}}" @if(@$detail && $detail->ms_asset_type_id == $kh->id) selected @endif>{{$kh->jenis_harta}} {{$kh->kelompok_harta}} ({{$kh->masa_manfaat}} tahun)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Nama Harta</label>
                    <input type="text" class="form-control" name="name" placeholder="Nama Harta" value="{{ @$detail ? $detail->name : ''}}" required>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Tipe Depresiasi</label>
                    <select class="form-control" name="depreciation_type" placeholder="Tipe Depresiasi" required>
                        <option @if(@$detail && $detail->depreciation_type == 'GARIS LURUS') selected @endif>GARIS LURUS</option>
                        <option @if(@$detail && $detail->depreciation_type == 'SALDO MENURUN') selected @endif>SALDO MENURUN</option>
                        <option @if(@$detail && $detail->depreciation_type == 'CUSTOM') selected @endif>CUSTOM</option>
                    </select>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Tanggal Perolehan</label>
                    <input type="text" class="form-control datepicker" name="date" placeholder="Tanggal Perolehan" data-date-format="yyyy-mm-dd" value="{{@$detail->date}}" required>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Harga Perolehan</label>
                    <input type="text" class="form-control" name="price" placeholder="Harga Perolehan" value="{{@$detail->price}}" required>
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
  $('.datepicker').datepicker({
            autoclose: true
        });
</script>