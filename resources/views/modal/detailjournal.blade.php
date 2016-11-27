<div class="row">
	<div class="col-sm-6">
		<table>
			<tr>
				<td width="120"><strong>Journal No</strong></td><td width="15">:</td><td>{{$fetch[0]->ledg_number}}</td>
			</tr>
			<tr>
				<td><strong>Date</strong></td><td width="15">:</td><td>{{date('d F Y',strtotime($fetch[0]->ledg_date))}}</td>
			</tr>
			<tr>
				<td><strong>Ref No</strong></td><td width="15">:</td><td>{{$fetch[0]->ledg_refno}}</td>
			</tr>
			<tr>
				<td><strong>Journal Type</strong></td><td width="15">:</td><td>{{$fetch[0]->jour_type_name}}</td>
			</tr>
		</table>
	</div>

</div>
<br><br>
<div class="row">
	<div class="col-sm-12">
		<table width="100%" class="table table-bordered">
			<thead class="text-center" style="font-weight:bold">
				<tr>
	              <td>COA Code</td>
	              <td>COA Name</td>
	              <td>Description</td>
	              <td>Department</td>
	              <td>Debit</td>
	              <td>Credit</td>
          		</tr>
            </thead>
            <?php 
            	$totalDebet = 0; 
            	$totalCredit = 0;
            ?>
            @foreach($fetch as $ledger)
            <tr>
            	<td>{{$ledger->coa_code}}</td>
            	<td>{{$ledger->coa_name}}</td>
            	<td>{{$ledger->ledg_description}}</td>
            	<td>{{$ledger->dept_name}}</td>
            	<td>@if(empty((int)$ledger->ledg_debit)){{'-'}}@else{{"Rp. ".number_format($ledger->ledg_debit)}}@endif</td>
            	<td>@if(empty((int)$ledger->ledg_credit)){{'-'}}@else{{"Rp. ".number_format($ledger->ledg_credit)}}@endif</td>
            </tr>
            <?php 
            	$totalDebet+=$ledger->ledg_debit;
            	$totalCredit+=$ledger->ledg_credit;
            ?>
            @endforeach
            <tr>
            	<td colspan="4">Total</td>
            	<td>{{"Rp. ".number_format($totalDebet) }}</td>
            	<td>{{"Rp. ".number_format($totalCredit) }}</td>
            </tr>
		</table>
	</div>
</div>