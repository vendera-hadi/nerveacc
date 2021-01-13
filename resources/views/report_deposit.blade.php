<table class="table table-stripped" width="100%">
	<thead>
      <tr>
        <th>No.Unit</th>
        <th>Nama Tenan</th>
        <th>Tanggal</th>
        <th>Nomor FO</th>
        <th>Nilai FO</th>
        <th>Nilai Adm</th>
        <th>Total</th>
        <th>Ref</th>
        <th>No.Out</th>
        <th>Tanggal</th>
        <th>Amount</th>
        <th>Pemotongan</th>
        <th>Saldo</th>
      </tr>
    </thead>
    <tbody>
    @php
    $total_masuk = 0;
    $total_keluar = 0;
    $total_saldo = 0;
    $total_fo = 0;
    $total_adm = 0;
    $total_potong = 0;
    @endphp
    @foreach($invoices as $inv)
    <tr>
        <td>{{$inv->unit_code}}</td>
    	<td>{{$inv->tenan_name}}</td>
        <td>{{date('m/d/Y',strtotime($inv->fit_date))}}</td>
        <td>{{$inv->fit_number}}</td>
        <td style="text-align: right;">{{number_format($inv->fitout,2)}}</td>
        <td style="text-align: right;">{{number_format($inv->admin,2)}}</td>
        <td style="text-align: right;">{{number_format($inv->fit_amount,2)}}</td>
        <td>{{$inv->fit_refno}}</td>
        <td>{{$inv->out_number}}</td>
        <td>{{ ($inv->out_date == NULL ? '' : date('m/d/Y',strtotime($inv->out_date)))}}</td>
        <td style="text-align: right;">{{number_format($inv->out_amount,2)}}</td>
        <?php
            if($inv->fitout == 0){
                $saldo = 0;
                $potong = 0;
            }else if($inv->fitout - $inv->out_amount > 0 && $inv->out_number != NULL){
                $potong = $inv->fitout - $inv->out_amount;
                $saldo = 0;
            }else{
                $potong = 0;
                $saldo = $inv->fitout - $inv->out_amount;
            }
        ?>
        <td style="text-align: right;">{{number_format($potong,2)}}</td>
        <td style="text-align: right;">{{number_format($saldo,2)}}</td>
    </tr>
    @php
    $total_fo = $total_fo + $inv->fitout;
    $total_adm = $total_adm + $inv->admin;
    $total_masuk = $total_masuk + $inv->fit_amount;
    $total_keluar = $total_keluar + $inv->out_amount;
    $total_potong = $total_potong + $potong;
    $total_saldo = $total_saldo + $saldo;
    @endphp
    @endforeach
    <tr>
        <td colspan="4">SALDO</td>
        <td style="text-align: right;">{{number_format($total_fo,2)}}</td>
        <td style="text-align: right;">{{number_format($total_adm,2)}}</td>
        <td style="text-align: right;">{{number_format($total_masuk,2)}}</td>
        <td colspan="3">&nbsp;</td>
        <td style="text-align: right;">{{number_format($total_keluar,2)}}</td>
        <td style="text-align: right;">{{number_format($total_potong,2)}}</td>
        <td style="text-align: right;">{{number_format($total_saldo,2)}}</td>
    </tr>
    </tbody>
</table>