<div class="row">
	<div class="col-sm-6">
		<strong>Date :</strong> {{date('d F Y',strtotime($fetch[0]->ledg_date))}}<br>
		<strong>Ref No :</strong> {{$fetch[0]->ledg_refno}}<br>
		<strong>Department :</strong> {{$fetch[0]->dept_name}}<br>
		<strong>Journal Type :</strong> {{$fetch[0]->jour_type_name}}<br>
	</div>

	<div class="col-sm-6">
		<strong>Description :</strong><br>{{$fetch[0]->ledg_description}}
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
	              <td>Debit</td>
	              <td>Credit</td>
          		</tr>
            </thead>
            @foreach($fetch as $ledger)
            <tr>
            	<td>{{$ledger->coa_code}}</td>
            	<td>{{$ledger->coa_name}}</td>
            	<td>{{$ledger->ledg_debit}}</td>
            	<td>{{$ledger->ledg_credit}}</td>
            </tr>
            @endforeach
		</table>
	</div>
</div>