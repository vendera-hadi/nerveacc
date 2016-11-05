<div class="row">
	<div class="col-sm-6">
		<strong>Date :</strong> {{date('d F Y',strtotime($fetch[0]->ledg_date))}}<br>
		<strong>Ref No :</strong> {{$fetch[0]->ledg_refno}}<br>
		<strong>Journal Type :</strong> {{$fetch[0]->jour_type_name}}<br>
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
            @foreach($fetch as $ledger)
            <tr>
            	<td>{{$ledger->coa_code}}</td>
            	<td>{{$ledger->coa_name}}</td>
            	<td>{{$ledger->ledg_description}}</td>
            	<td>{{$ledger->dept_name}}</td>
            	<td>@if(empty($ledger->ledg_debit)){{'-'}}@else{{$ledger->ledg_debit}}@endif</td>
            	<td>@if(empty($ledger->ledg_credit)){{'-'}}@else{{$ledger->ledg_credit}}@endif</td>
            </tr>
            @endforeach
		</table>
	</div>
</div>