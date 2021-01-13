<table class="table table-stripped" width="100%">
	<thead>
      <tr>
        <th>Tanggal</th>
        <th>No.Unit</th>
        <th>Nama</th>
        <th>Amount</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
    <tr>
        <td>{{date('m/d/Y',strtotime($inv->va_date))}}</td>
        <td>{{$inv->unit_code}}</td>
    	<td>{{$inv->tenan_name}}</td>
        <td style="text-align: right;">{{number_format($inv->va_amount,2)}}</td>
    </tr>
    @endforeach
    </tbody>
</table>