<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th>No.Unit</th>
        <th>Nama Tenan</th>
        <th>Tanggal</th>
        <th>Nomor Inv</th>
        <th>Type</th>
        <th>Total</th>
        <th>Ref</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
    <tr>
        <td>{{$inv->unit_code}}</td>
        <td>{{$inv->tenan_name}}</td>
        <td>{{date('m/d/Y',strtotime($inv->manual_date))}}</td>
        <td>{{$inv->manual_number}}</td>
        <td>{{$inv->name}}</td>
        <td style="text-align: right;">{{number_format($inv->manual_amount,2)}}</td>
        <td>{{$inv->manual_refno}}</td>
    </tr>
    @endforeach
    </tbody>
</table>