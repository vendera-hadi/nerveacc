<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th>No.Unit</th>
        <th>Nama Tenant</th>
        <th>No.HP</th>
        <th>Total Tagihan</th>
        <th>Maintenance</th>
        <th>Utilities</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
    <tr>
        <td>{{$inv->unit_code}}</td>
        <td>{{$inv->tenan_name}}</td>
        <td>{{$inv->tenan_phone}}</td>
        <td style="text-align: right;">{{number_format($inv->total,2)}}</td>
        <td style="text-align: right;">{{number_format($inv->maintenance,2)}}</td>
        <td style="text-align: right;">{{number_format($inv->utilities,2)}}</td>
    </tr>
    @endforeach
    </tbody>
</table>