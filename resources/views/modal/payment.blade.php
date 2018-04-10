<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
	  <li class="active"><a href="#modaltab_1" data-toggle="tab">Information</a></li>
	  <li><a href="#modaltab_2" data-toggle="tab">Payment Detail</a></li>
	</ul>
	<div class="tab-content">
	  <div class="tab-pane active" id="modaltab_1">
	    	<!-- information -->
	    	<table width="100%">
			<tr><td width="40%"><strong>Tenant Name</strong></td><td>:</td><td>{{$header->tenant->tenan_name}}</td></tr>
			<tr><td width="40%"><strong>Payment Code</strong></td><td>:</td><td>{{$header->invpayh_checkno}}</td></tr>
			<tr><td><strong>Payment Date</strong></td><td>:</td><td>{{date('d/m/Y', strtotime($header->invpayh_date))}}</td></tr>
			<tr><td><strong>Payment Total</strong></td><td>:</td><td>{{'Rp. '.number_format($header->invpayh_amount)}}</td></tr>
			</table>
	    	<!-- end information -->
	  </div>
	  <!-- /.tab-pane -->
	  <div class="tab-pane" id="modaltab_2">
			<table class="table table-hover table-bordered">
			<thead>
			    <tr>
			        <th width="100">No.Invoice</th>
			        <th width="80">Unit</th>
			        <th width="80">Contract</th>
			        <th width="80">Amount</th>
			    </tr>
			</thead>
			@if($header->TrInvoicePaymdtl->count() > 0)
			<tbody>
			    @foreach($header->TrInvoicePaymdtl as $value)
			    <tr>
			        <td>{{!empty(@$value->TrInvoice->inv_number) ? $value->TrInvoice->inv_number : '(Deleted Invoice)' }}</td>
			        <td>{{@$value->TrInvoice->TrContract->MsUnit->unit_code}}</td>
			        <td>{{@$value->TrInvoice->TrContract->contr_no}}</td>
			        <td>{{'Rp. '.number_format($value->invpayd_amount)}}</td>
			    </tr>
			    @endforeach
			</tbody>
			@endif
			</table>
	  </div>

	</div>
	<!-- /.tab-content -->
</div>
