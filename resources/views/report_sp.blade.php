<table class="table table-stripped" width="100%">
    <thead>
      <tr>
        <th>No.</th>
        <th>Reminder No</th>
        <th>Unit</th>
        <th>Nama Tenant</th>
        <th>Phone</th>
        <th>Tanggal</th>
        <th>Pokok</th>
        <th>Denda</th>
        <th>Total</th>
        <th>Last Send</th>
        <th>SP</th>
      </tr>
    </thead>
    <tbody>
    @php $no = 1; @endphp
    @foreach($invoices as $inv)
    <tr>
        <td>{{$no}}</td>
        <td>{{$inv->reminder_no}}</td>
        <td>{{$inv->unit_code}}</td>
        <td>{{$inv->tenan_name}}</td>
        <td>{{$inv->tenan_phone}}</td>
        <td>{{$inv->reminder_date}}</td>
        <td style="text-align: right;">{{number_format($inv->pokok_amount,2)}}</td>
        <td style="text-align: right;">{{number_format($inv->denda_total,2)}}</td>
        <td style="text-align: right;">{{number_format($inv->denda_outstanding,2)}}</td>
        <td>{{$inv->lastsent_date}}</td>
        <td>
            <?php
            switch ($inv->sp_type) {
                case '4':
                        $sp = 'SP 1';
                        break;
                case '5':
                        $sp = 'SP 2';
                        break;
                case '6':
                        $sp = 'SP 3';
                        break;
                 default:
                        $sp = 'Unidentified';
                     break;
             }
             echo $sp;
            ?>
        </td>
    </tr>
    @php $no++; @endphp
    @endforeach
    </tbody>
</table>