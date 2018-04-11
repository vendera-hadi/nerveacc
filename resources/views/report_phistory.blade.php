<table class="table table-bordered" width="100%">
    <thead>
      <tr>
        <th style="text-align: center;">No Invoice / No Kwitansi</th>
        <th style="text-align: center;">Tgl Invoice /  Tgl Kwitansi</th>
        <th style="text-align: center;">Supplier</th>
        <th style="text-align: center;">Bank</th>
        <th style="text-align: center;">Debet (Rp)</th>
        <th style="text-align: center;">Kredit (Rp)</th>
      </tr>
    </thead>
    <tbody>
        <?php
            $total_debet = 0;
            $total_kredit = 0;
        ?>
        @foreach($invoices as $inv)
        <tr>
            <td>{{$inv['kode']}}</td>
            <td>{{date('d/m/Y',strtotime($inv['tgl']))}}</td>
            <td>{{$inv['spl_name']}}</td>
            <td>{{$inv['bank']}}</td>
            <td style="text-align: right">{{number_format($inv['debet'])}}</td>
            <td style="text-align: right">{{number_format($inv['kredit'])}}</td>
        </tr>
        <?php $total_debet += $inv['debet']; $total_kredit += $inv['kredit']; ?>
        @endforeach
        <tr>
            <td colspan="4" style="text-align: center;font-weight: bold;">TOTAL</td>
            <td style="text-align: right">{{number_format($total_debet)}}</td>
            <td style="text-align: right">{{number_format($total_kredit)}}</td>
        </tr>
    </tbody>
    <tfoot>
        
    </tfoot>
</table>