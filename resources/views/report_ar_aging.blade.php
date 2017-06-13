<h4><?php 
echo $title_r; 
if(count($unit) > 0){ echo '<br>Unit '.$unit[0]->unit_code; } ?></h4>
<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th style="text-align: center;">Unit</th>
        <th style="text-align: center;">Tenant</th>
        <th style="text-align: center;">Total</th>   
        <th style="text-align: center;">{{$label[0]}}</th>
        <th style="text-align: center;">{{$label[1]}}</th> 
        <th style="text-align: center;">{{$label[2]}}</th>
        <th style="text-align: center;">{{$label[3]}}</th>     
      </tr>
    </thead>
        <tbody>
            <?php $total = 0; $total_30 = 0; $total_60 = 0; $total_90 = 0; $total_180 = 0; ?>
            @foreach($invoices as $invoice)
            <tr style="text-align: right;">
                <td>{{$invoice->unit_code}}</td>
                <td>{{$invoice->tenan_name}}</td>
                <td>{{str_replace(',','.',number_format($invoice->total,2))}}</td>
                <td>{{str_replace(',','.',number_format($invoice->ag30,2))}}</td>
                <td>{{str_replace(',','.',number_format($invoice->ag60,2))}}</td>
                <td>{{str_replace(',','.',number_format($invoice->ag90,2))}}</td>
                <td>{{str_replace(',','.',number_format($invoice->agl180,2))}}</td>
            </tr>
            <?php
                $total = $total + $invoice->total;
                $total_30 = $total_30 + $invoice->ag30;
                $total_60 = $total_60 + $invoice->ag60;
                $total_90 = $total_90 + $invoice->ag90;
                $total_180 = $total_180 + $invoice->agl180;
            ?>
            @endforeach
            <tr><td colspan="7">&nbsp;</td></tr>
            <tr style="text-align: right;" class="info">
                <td colspan="2" style="text-align: center;font-weight: bold;">GRAND TOTAL</td>
                <td>{{str_replace(',','.',number_format($total,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_30,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_60,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_90,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_180,2))}}</td>
            </tr>
        </tbody>
</table>