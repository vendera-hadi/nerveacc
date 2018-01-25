<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">{{$title}} Harta</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
    <!-- isi form -->
    <form method="POST" action="{{$action}}" enctype="multipart/form-data">
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

            @if(!empty(@$detail->image))
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Image</label><br>
                    <img src="/upload/{{$detail->image}}" width="200">
                </div>
            </div>
            @endif

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Upload Image</label>
                    <input type="file" name="image" />
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>COA Aktiva</label>
                    <div class="input-group input-group-md">
                        <select class="js-example-basic-single" name="aktiva_coa_code" style="width:100%">
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
                    <label>Group Aktiva (Create & Edit Group <a href="{{url('groupaccount')}}">disini</a>)</label>
                    <select class="form-control" name="group_account_id" placeholder="Group Account ID">
                        <option value="">No group account</option>
                        @foreach($group_accounts as $acc)
                        <option value="{{$acc->id}}" @if(@$detail && $detail->group_account_id == $acc->id) selected @endif>{{$acc->grpaccn_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Supplier</label>
                    <select class="form-control" name="supplier_id" placeholder="Supplier">
                        <option value="">No supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{$supplier->id}}" @if(@$detail && $detail->supplier_id == $supplier->id) selected @endif>{{$supplier->spl_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>PO no.</label>
                    <input type="text" class="form-control" name="po_no" placeholder="Nomor PO (jika ada)" value="{{ @$detail ? $detail->po_no : ''}}">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Kode Induk</label>
                    <input type="text" class="form-control" name="kode_induk" placeholder="Kode Induk (jika ada)" value="{{ @$detail ? $detail->kode_induk : ''}}">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Cabang</label>
                    <input type="text" class="form-control" name="cabang" placeholder="Kode Induk (jika ada)" value="{{ @$detail ? $detail->cabang : ''}}">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Lokasi</label>
                    <input type="text" class="form-control" name="lokasi" placeholder="Kode Induk (jika ada)" value="{{ @$detail ? $detail->lokasi : ''}}">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Area</label>
                    <input type="text" class="form-control" name="area" placeholder="Area (jika ada)" value="{{ @$detail ? $detail->area : ''}}">
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Departemen</label>
                    <input type="text" class="form-control" name="departemen" placeholder="Departemen (jika ada)" value="{{ @$detail ? $detail->departemen : ''}}" required>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>User / Pengguna</label>
                    <input type="text" class="form-control" name="user" placeholder="User / Pengguna" value="{{ @$detail ? $detail->user : ''}}" required>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Kondisi</label>
                    <input type="text" class="form-control" name="kondisi" placeholder="Kondisi Barang" value="{{ @$detail ? $detail->kondisi : ''}}" required>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Tanggal Perolehan</label>
                    <input type="text" class="form-control datepicker" name="date" placeholder="Tanggal Perolehan" data-date-format="yyyy-mm-dd" value="@if(!empty(@$detail->date)){{date('Y-m-d',strtotime(@$detail->date))}}@endif" required>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label>Harga Perolehan</label>
                    <input type="text" class="form-control" name="price" placeholder="Harga Perolehan" value="@if(!empty(@$detail->price)){{number_format(@$detail->price,0,',','')}}@endif" required>
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

    $(function(){
        $(".js-example-basic-single").select2();

        @if(@$detail)
        $("select[name=aktiva_coa_code]").val("{{str_replace(" ","", @$detail->aktiva_coa_code)}}").trigger("change");
        @endif

    });
</script>