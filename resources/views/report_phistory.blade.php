<table class="table table-bordered" width="100%">
    <thead>
      <tr>
        <th style="text-align: center;">No Invoice / Tgl Invoice</th>
        <th style="text-align: center;">No. Kwitansi /  Tgl Kwitansi</th>
        <th style="text-align: center;">Supplier</th>
        <th style="text-align: center;">Bank</th>
        <th style="text-align: center;">Total (Rp)</th>
        <!-- <th style="text-align: center;">Kredit (Rp)</th> -->
      </tr>
    </thead>
    <tbody>
        <?php
            $total_debet = 0;
            $total_kredit = 0;
        ?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv->invoice_no."  (".date('d/m/Y',strtotime($inv->invoice_date)).")"}}</td>
            <td>{{$inv->payment_code."  (".date('d/m/Y',strtotime($inv->payment_date)).")"}}</td>
            <td>{{$inv->spl_name}}</td>
            <td>{{@$inv->cashbk_name ?: "CASH"}}</td>
            <td style="text-align: right">{{number_format($inv->amount)}}</td>
        </tr>
        <?php $total_debet += $inv->amount; ?>
        @endforeach
        <tr>
            <td colspan="4" style="text-align: center;font-weight: bold;">TOTAL</td>
            <td style="text-align: right">{{number_format($total_debet)}}</td>
        </tr>
    </tbody>
    <tfoot>

    </tfoot>
</table>