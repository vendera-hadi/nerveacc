<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th>No.Unit</th>
        <th>Nama Tenan</th>
        <th>Tanggal</th>
        <th>Nomor Reminder</th>
        <th>Bank</th>
        <th>Note</th>
        <th>Total</th>
        <th>Status Posting</th>
        <th>Status Void</th>
      </tr>
    </thead>
    <tbody>
    @foreach($invoices as $inv)
    <tr>
        <td>{{$inv->unit_code}}</td>
        <td>{{$inv->tenan_name}}</td>
        <td>{{date('m/d/Y',strtotime($inv->denda_date))}}</td>
        <td>{{$inv->denda_number}}</td>
        <td style="text-align: right;">{{$inv->cashbk_name}}</td>
        <td style="text-align: right;">{{$inv->denda_keterangan}}</td>
        <td style="text-align: right;">{{number_format($inv->denda_amount,2)}}</td>
        <td>{{($inv->posting == 1 ? 'yes' : 'no')}}</td>
        <td>{{($inv->status_void == 1 ? 'yes' : 'no')}}</td>
    </tr>
    @endforeach
    </tbody>
</table>