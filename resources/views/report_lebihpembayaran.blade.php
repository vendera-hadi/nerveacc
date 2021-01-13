<table class="table table-stripped table-bordered" width="100%">
    <thead>
      <tr>
        <th width="10">No.</th>
        <th>Tanggal</th>
        <th>Unit</th>
        <th>Tenant</th>
        <th>Saldo Deposit</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $no = 1; 
    $total = 0;
    for($i=0; $i<count($invoices); $i++){
        $total = $total + $invoices[$i]['excess_amount'];
    ?>
    <tr>
        <td>{{$no}}</td>
        <td>{{$invoices[$i]['invpayh_date']}}</td>
        <td>{{$invoices[$i]['unit_code']}}</td>
        <td>{{$invoices[$i]['ms_tenant']}}</td>
        <td style="text-align: right;">{{number_format($invoices[$i]['excess_amount'],0)}}</td>
    </tr>
    <?php 
    $no++; 
    }
    ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>TOTAL</td>
        <td style="text-align: right;"><?php echo number_format($total,0) ?></td>
    </tr>
    </tbody>
</table>