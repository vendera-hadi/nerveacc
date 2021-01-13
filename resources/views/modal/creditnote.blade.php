<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
	  <li class="active"><a href="#modaltab_1" data-toggle="tab">Information</a></li>
	  <li><a href="#modaltab_2" data-toggle="tab">Credit Note Detail</a></li>
	</ul>
	<div class="tab-content">
	  <div class="tab-pane active" id="modaltab_1">
	    	<table width="100%">
				<tr>
					<td width="40%"><strong>Credit Note Number</strong></td>
					<td>:</td>
					<td>{{$header->creditnote_number}}</td>
				</tr>
				<tr>
					<td><strong>Credit Note Date</strong></td>
					<td>:</td>
					<td>{{date('d/m/Y', strtotime($header->creditnote_date))}}</td>
				</tr>
				<tr>
					<td width="40%"><strong>Remarks</strong></td>
					<td>:</td>
					<td>{{$header->creditnote_keterangan}}</td>
				</tr>
			</table>
	  </div>
	  <!-- /.tab-pane -->
	  <div class="tab-pane" id="modaltab_2">
			<table class="table table-hover table-bordered">
			<thead>
			    <tr>
			        <th width="100">No.Invoice</th>
			        <th width="80">COA</th>
			        <th width="80">Journal Type</th>
			        <th width="80">Amount</th>
			    </tr>
			</thead>
			@if(count($detail) > 0)
			<tbody>
			    @foreach($detail as $value)
			    <tr>
			        <td>{{!empty(@$value->inv_number) ? $value->inv_number : '(Deleted Invoice)' }}</td>
			        <td>{{@$value->coa_code}}</td>
			        <td>{{@$value->jurnal_type}}</td>
			        <td>{{'Rp. '.number_format($value->credit_amount)}}</td>
			    </tr>
			    @endforeach
			</tbody>
			@endif
			</table>
	  </div>

	</div>
	<!-- /.tab-content -->
</div>
