@if($all == 0)
<table class="table table-bordered" width="100%">
    <thead>
      <tr>
        <th style="text-align: center;">Kode Supplier</th>
        <th style="text-align: center;">Nama Supplier</th>
        <th style="text-align: center;">Total (Rp)</th>
      </tr>
    </thead>
    <tbody>
    <?php
        $total_bayar = 0;
    ?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv->spl_code}}</td>
            <td>{{$inv->spl_name}}</td>
            <td style="text-align: right">{{number_format($inv->total)}}</td>
        </tr>
        <?php $total_bayar += $inv->total; ?>
        @endforeach
        <tr>
            <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
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
        <th style="text-align: center;">Kode Supplier</th>
        <th style="text-align: center;">Nama Supplier</th>
        <th style="text-align: center;">Total Belum Bayar (Rp)</th>
        <th style="text-align: center;">Total Sudah Bayar (Rp)</th>
      </tr>
    </thead>
    <tbody>
    <?php
        $total_bayar_npaid = 0;
        $total_bayar_paid = 0;
    ?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv['spl_code']}}</td>
            <td>{{$inv['spl_name']}}</td>
            <td style="text-align: right">{{number_format($inv['npaid'])}}</td>
            <td style="text-align: right">{{number_format($inv['paid'])}}</td>
        </tr>
        <?php $total_bayar_npaid += $inv['npaid']; $total_bayar_paid += $inv['paid']; ?>
        @endforeach
        <tr>
            <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
            <td style="text-align: right">{{number_format($total_bayar_npaid)}}</td>
            <td style="text-align: right">{{number_format($total_bayar_paid)}}</td>
        </tr>
    </tbody>
    <tfoot>
        
    </tfoot>
</table>
@endif