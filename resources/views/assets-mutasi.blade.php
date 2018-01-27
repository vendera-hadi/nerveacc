<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">History Mutasi - {{$asset->name}}</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
  <table class="table table-striped">
    <thead>
        <tr>
            <th>Tgl</th>
            <th>Kode Induk</th>
            <th>Cabang</th>
            <th>Lokasi</th>
            <th>Area</th>
            <th>Departemen</th>
            <th>Pemakai</th>
            <th>Kondisi</th>
        </tr>

    </thead>
    <tbody>
        @foreach($asset->mutasi as $mutasi)
        <tr>
          <td>{{date('d/m/Y',strtotime($mutasi->created_at))}}</td>
          <td>{{$mutasi->kode_induk}}</td>
          <td>{{$mutasi->cabang}}</td>
          <td>{{$mutasi->lokasi}}</td>
          <td>{{$mutasi->area}}</td>
          <td>{{$mutasi->departemen}}</td>
          <td>{{$mutasi->user}}</td>
          <td>{{$mutasi->kondisi}}</td>
        </tr>
        @endforeach
    </tbody>
  </table>
</div>