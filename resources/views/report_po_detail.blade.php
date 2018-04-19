@if($all == 2)
<table class="table table-bordered" width="100%">
    <thead>
      <tr>
        <th style="text-align: center;">No.PO</th>
        <th style="text-align: center;">Supplier</th>
        <th style="text-align: center;">Total (Rp)</th>
        <th style="text-align: center;">No. AP</th>
        <th style="text-align: center;">Tanggal AP</th>
        <th style="text-align: center;">Jatuh Tempo</th>
        <th style="text-align: center;">Note</th>

      </tr>
    </thead>
    <tbody>
    <?php
        $total_bayar = 0;
    ?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv->po_number}}</td>
            <td>{{$inv->spl_name}}</td>
            <td style="text-align: right">{{number_format($inv->total)}}</td>
            <td>{{$inv->invoice_no}}</td>
            <td>{{date('d/m/Y',strtotime($inv->invoice_date))}}</td>
            <td>{{date('d/m/Y',strtotime($inv->invoice_duedate))}}</td>
            <td>{{$inv->note}}</td>

        </tr>
        <?php $total_bayar += $inv->total; ?>
        @endforeach
        <tr>
            <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
            <td style="text-align: right">{{number_format($total_bayar)}}</td>
            <td colspan="4">&nbsp;</td>
        </tr>
    </tbody>
    <tfoot>

    </tfoot>
</table>
@elseif($all == 3)
<table class="table table-bordered" width="100%">
    <thead>
      <tr>
        <th style="text-align: center;">Supplier</th>
        <th style="text-align: center;">No. AP</th>
        <th style="text-align: center;">No. Kwitansi</th>
        <th style="text-align: center;">Tanggal Payment</th>
        <th style="text-align: center;">Note</th>
        <th style="text-align: center;">Total (Rp)</th>
      </tr>
    </thead>
    <tbody>
    <?php
        $total_bayar = 0;
    ?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv->spl_name}}</td>
            <td>{{$inv->invoice_no}}</td>
            <td>{{$inv->payment_code}}</td>
            <td>{{date('d/m/Y',strtotime($inv->payment_date))}}</td>
            <td>{{$inv->note}}</td>
            <td style="text-align: right">{{number_format($inv->amount)}}</td>
        </tr>
        <?php $total_bayar += $inv->amount; ?>
        @endforeach
        <tr>
            <td colspan="5" style="text-align: center;font-weight: bold;">TOTAL</td>
            <td style="text-align: right">{{number_format($total_bayar)}}</td>
        </tr>
    </tbody>
    <tfoot>

    </tfoot>
</table>
@else
<table class="table table-bordered" width="100%">
    <thead>
      <tr>
        @if(empty(@$no_po))
        <th style="text-align: center;">PO</th>
        @endif
        <th style="text-align: center;">Supplier</th>
        <th style="text-align: center;">Tgl Invoice/ No Invoice</th>
        <th style="text-align: center;">Tgl Kwitansi/No. Kwitansi</th>
        <th style="text-align: center;">Total (Rp)</th>
      </tr>
    </thead>
    <tbody>
        @foreach($invoices as $inv)
        <tr>
            @if(empty(@$no_po))
            <td>{{$inv['po_number']}}</td>
            @endif
            <td>{{$inv['spl_name']}}</td>
            <td style="text-align: center;">{{date('d F Y',strtotime($inv['invoice_date'])).' / '.$inv['invoice_no']}}</td>
            <td style="text-align: center;">
                @foreach($inv->payment as $paym)
                {!!date('d F Y',strtotime($paym->header->payment_date)).' / '.$paym->header->payment_code.'<br>'!!}
                @endforeach
            </td>
            <td style="text-align: right">{{number_format($inv['total'])}}</td>
        </tr>
        <!-- jabarin detail -->
            @foreach($inv->detail as $dtl)
            <tr>
                <td></td>
                <td colspan="2">{{$dtl->note}}</td>
                <td colspan="2" style="text-align: right">{{number_format($dtl->amount)}}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>

    </tfoot>
</table>
@endif