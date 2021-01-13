<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th width="10">No.</th>
        <th>Unit</th>
        <th>Current Lebih Bayar</th>
      </tr>
    </thead>
    <tbody>
    @php $no = 1; @endphp
    @foreach($invoices as $inv)
    <tr>
        <td>{{$no}}</td>
        <td>{{$inv->unit_code}}</td>
        <td style="text-align: right;">{{number_format($inv->total_amount,2)}}</td>
    </tr>
    @php $no++; @endphp
    @endforeach
    </tbody>
</table>