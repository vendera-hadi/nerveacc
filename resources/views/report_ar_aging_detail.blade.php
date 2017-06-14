<h4><?php 
echo $title_r; 
if(count($unit) > 0){ echo '<br>Unit '.$unit[0]->unit_code; } ?></h4>
@if($tyt == 1)
<table class="table table-bordered" width="100%"> 
    <thead>
      <tr>
        <th style="text-align: center;">No Invoice</th>
        <th style="text-align: center;">Invoice Date</th>
        <th style="text-align: center;">Due Date</th>
        <th style="text-align: center;">Total</th>   
        <th style="text-align: center;">{{$label[0]}}</th>
        <th style="text-align: center;">{{$label[1]}}</th> 
        <th style="text-align: center;">{{$label[2]}}</th>
        <th style="text-align: center;">{{$label[3]}}</th>     
      </tr>
    </thead>
    <tbody>
    	<?php $gtotal = 0; $gtotal_30 = 0; $gtotal_60 = 0; $gtotal_90 = 0; $gtotal_180 = 0; ?>
    	@foreach($invoices as $invoice)
    	<tr><td colspan="8">CUSTOMER : <?php echo 'UNIT '.$invoice['unit_code'].' - '.$invoice['tenan_name']; ?></td></tr>
    		<?php $total = 0; $total_30 = 0; $total_60 = 0; $total_90 = 0; $total_180 = 0; ?>
	    	@foreach($invoice['details'] as $detail)
	    	<tr>
	    		<td style="text-align: center;"><?php echo $detail['inv_number'] ?></td>
	    		<td style="text-align: center;"><?php echo $detail['tanggal'] ?></td>
	    		<td style="text-align: center;"><?php echo $detail['tanggaldue'] ?></td>
	    		<td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['inv_outstanding'],2)) ?></td>
	    		<td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags30'],2)) ?></td>
	    		<td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags60'],2)) ?></td>
	    		<td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags90'],2)) ?></td>
	    		<td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags180'],2)) ?></td>
	    	</tr>
	    	<?php
                $total = $total + $detail['inv_outstanding'];
                $total_30 = $total_30 + $detail['ags30'];
                $total_60 = $total_60 + $detail['ags60'];
                $total_90 = $total_90 + $detail['ags90'];
                $total_180 = $total_180 + $detail['ags180'];
            ?>
	    	@endforeach
	    	<tr style="text-align: right;">
                <td colspan="3" style="text-align: center;font-weight: bold;">TOTAL</td>
                <td>{{str_replace(',','.',number_format($total,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_30,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_60,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_90,2))}}</td>
                <td>{{str_replace(',','.',number_format($total_180,2))}}</td>
            </tr>
            <?php
                $gtotal = $gtotal + $total;
                $gtotal_30 = $gtotal_30 + $total_30;
                $gtotal_60 = $gtotal_60 + $total_60;
                $gtotal_90 = $gtotal_90 + $total_90;;
                $gtotal_180 = $gtotal_180 + $total_180;;
            ?>
    	@endforeach
    	<tr><td colspan="8">&nbsp;</td></tr>
    	<tr><td colspan="8">GRAND TOTAL</td></tr>
    	<tr style="text-align: right;" class="info">
            <td colspan="3" style="text-align: center;font-weight: bold;">TOTAL</td>
            <td>{{str_replace(',','.',number_format($gtotal,2))}}</td>
            <td>{{str_replace(',','.',number_format($gtotal_30,2))}}</td>
            <td>{{str_replace(',','.',number_format($gtotal_60,2))}}</td>
            <td>{{str_replace(',','.',number_format($gtotal_90,2))}}</td>
            <td>{{str_replace(',','.',number_format($gtotal_180,2))}}</td>
        </tr>
    </tbody>
</table>
@else
    @if($ty == 3)
        <table class="table table-bordered" width="100%"> 
            <thead>
              <tr>
                <th style="text-align: center;">No Invoice</th>
                <th style="text-align: center;">Invoice Date</th>
                <th style="text-align: center;">Type</th>   
                <th style="text-align: center;">{{$label[0]}}</th>
                <th style="text-align: center;">{{$label[1]}}</th> 
                <th style="text-align: center;">{{$label[2]}}</th>
                <th style="text-align: center;">{{$label[3]}}</th>     
              </tr>
            </thead>
            <tbody>
                <?php $gtotal = 0; $gtotal_30 = 0; $gtotal_60 = 0; $gtotal_90 = 0; $gtotal_180 = 0; ?>
                @foreach($invoices as $invoice)
                <tr><td colspan="7">CUSTOMER : <?php echo 'UNIT '.$invoice['unit_code'].' - '.$invoice['tenan_name']; ?></td></tr>
                    <?php $total = 0; $total_30 = 0; $total_60 = 0; $total_90 = 0; $total_180 = 0; ?>
                    @foreach($invoice['details'] as $detail)
                    <tr>
                        <td style="text-align: center;"><?php echo $detail['inv_number'] ?></td>
                        <td style="text-align: center;"><?php echo $detail['tanggal'] ?></td>
                        <td style="text-align: right;"><?php echo $detail['inv_tp'] ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags30'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags60'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags90'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags180'],2)) ?></td>
                    </tr>
                    <?php
                        $total = $total + $detail['inv_amount'];
                        $total_30 = $total_30 + $detail['ags30'];
                        $total_60 = $total_60 + $detail['ags60'];
                        $total_90 = $total_90 + $detail['ags90'];
                        $total_180 = $total_180 + $detail['ags180'];
                    ?>
                    @endforeach
                    <tr style="text-align: right;">
                        <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
                        <td>{{str_replace(',','.',number_format($total,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_30,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_60,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_90,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_180,2))}}</td>
                    </tr>
                    <?php
                        $gtotal = $gtotal + $total;
                        $gtotal_30 = $gtotal_30 + $total_30;
                        $gtotal_60 = $gtotal_60 + $total_60;
                        $gtotal_90 = $gtotal_90 + $total_90;;
                        $gtotal_180 = $gtotal_180 + $total_180;;
                    ?>
                @endforeach
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr><td colspan="7">GRAND TOTAL</td></tr>
                <tr style="text-align: right;" class="info">
                    <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
                    <td>{{str_replace(',','.',number_format($gtotal,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_30,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_60,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_90,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_180,2))}}</td>
                </tr>
            </tbody>
        </table>
    @else
        <table class="table table-bordered" width="100%"> 
            <thead>
              <tr>
                <th style="text-align: center;">No Invoice</th>
                <th style="text-align: center;">Invoice Date</th>
                <th style="text-align: center;">Total</th>   
                <th style="text-align: center;">{{$label[0]}}</th>
                <th style="text-align: center;">{{$label[1]}}</th> 
                <th style="text-align: center;">{{$label[2]}}</th>
                <th style="text-align: center;">{{$label[3]}}</th>     
              </tr>
            </thead>
            <tbody>
                <?php $gtotal = 0; $gtotal_30 = 0; $gtotal_60 = 0; $gtotal_90 = 0; $gtotal_180 = 0; ?>
                @foreach($invoices as $invoice)
                <tr><td colspan="7">CUSTOMER : <?php echo 'UNIT '.$invoice['unit_code'].' - '.$invoice['tenan_name']; ?></td></tr>
                    <?php $total = 0; $total_30 = 0; $total_60 = 0; $total_90 = 0; $total_180 = 0; ?>
                    @foreach($invoice['details'] as $detail)
                    <tr>
                        <td style="text-align: center;"><?php echo $detail['inv_number'] ?></td>
                        <td style="text-align: center;"><?php echo $detail['tanggal'] ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['inv_amount'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags30'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags60'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags90'],2)) ?></td>
                        <td style="text-align: right;"><?php echo str_replace(',','.',number_format($detail['ags180'],2)) ?></td>
                    </tr>
                    <?php
                        $total = $total + $detail['inv_amount'];
                        $total_30 = $total_30 + $detail['ags30'];
                        $total_60 = $total_60 + $detail['ags60'];
                        $total_90 = $total_90 + $detail['ags90'];
                        $total_180 = $total_180 + $detail['ags180'];
                    ?>
                    @endforeach
                    <tr style="text-align: right;">
                        <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
                        <td>{{str_replace(',','.',number_format($total,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_30,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_60,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_90,2))}}</td>
                        <td>{{str_replace(',','.',number_format($total_180,2))}}</td>
                    </tr>
                    <?php
                        $gtotal = $gtotal + $total;
                        $gtotal_30 = $gtotal_30 + $total_30;
                        $gtotal_60 = $gtotal_60 + $total_60;
                        $gtotal_90 = $gtotal_90 + $total_90;;
                        $gtotal_180 = $gtotal_180 + $total_180;;
                    ?>
                @endforeach
                <tr><td colspan="7">&nbsp;</td></tr>
                <tr><td colspan="7">GRAND TOTAL</td></tr>
                <tr style="text-align: right;" class="info">
                    <td colspan="2" style="text-align: center;font-weight: bold;">TOTAL</td>
                    <td>{{str_replace(',','.',number_format($gtotal,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_30,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_60,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_90,2))}}</td>
                    <td>{{str_replace(',','.',number_format($gtotal_180,2))}}</td>
                </tr>
            </tbody>
        </table>
    @endif
@endif