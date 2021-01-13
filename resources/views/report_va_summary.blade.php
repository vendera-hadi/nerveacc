<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th>No.Unit</th>
        <th>No.Virtual</th>
        <th>Nama</th>
        <th>No.Invoice</th>
        <th>Tanggal Kadaluarsa</th>
        <th>Tanggal Jatuh Tempo</th>
        <th>Tagihan</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
    <tr>
        <td>{{$inv->unit_code}}</td>
        <td>{{($inv_tp == 1 ? $inv->va_utilities : $inv->va_maintenance)}}</td>
        <td>{{$inv->tenan_name}}</td>
        <td>{{$inv->inv_number}}</td>
        <td>{{date('m/d/Y',strtotime($inv->inv_duedate))}}</td>
        <td>{{date('m/d/Y',strtotime($inv->inv_duedate))}}</td>
        <td style="text-align: right;">{{number_format($inv->inv_amount,2)}}</td>
    </tr>
    @endforeach
    </tbody>
</table>