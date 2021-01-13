<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
	  <li class="active"><a href="#modaltab_1" data-toggle="tab">Information</a></li>
	  <li><a href="#modaltab_2" data-toggle="tab">Payment Detail</a></li>
	</ul>
	<div class="tab-content">
	  <div class="tab-pane active" id="modaltab_1">
	    	<!-- information -->
	    	<table width="100%">
			<tr><td width="40%"><strong>Payment Code</strong></td><td>:</td><td>{{$header->payment_code}}</td></tr>
			<tr><td width="40%"><strong>Supplier</strong></td><td>:</td><td>{{$header->supplier->spl_name}}</td></tr>
			<tr><td><strong>Payment Date</strong></td><td>:</td><td>{{date('d F Y, H:i', strtotime($header->payment_date))}}</td></tr>
			<tr><td><strong>Payment Total</strong></td><td>:</td><td>{{'Rp. '.number_format($header->amount)}}</td></tr>
			<tr><td><strong>Check/Giro No</strong></td><td>:</td><td>{{$header->check_no}}</td></tr>
			<tr><td><strong>Check/Giro Date</strong></td><td>:</td><td>@if(!empty($header->check_date)){{date('d F Y, H:i', strtotime($header->check_date))}}@endif</td></tr>
			</table>
	    	<!-- end information -->
	  </div>
	  <!-- /.tab-pane -->
	  <div class="tab-pane" id="modaltab_2">
			<table class="table table-hover table-bordered">
			<thead>
			    <tr>
			        <th width="100">No.Invoice</th>
			        <th width="80">Amount</th> 
			    </tr>
			</thead>
			@if($header->detail->count() > 0)
			<tbody>
			    @foreach($header->detail as $value)
			    <tr>
			        <td>{{!empty(@$value->apheader->invoice_no) ? $value->apheader->invoice_no : '(Deleted Invoice)' }}</td>
			        <td>{{'Rp. '.number_format($value->amount)}}</td>
			    </tr>
			    @endforeach
			</tbody>
			@endif
			<?php if($header->pajak_id != NULL){ ?>
				<tr>
			        <td>Pajak</td>
			        <td>{{'Rp. '.number_format($header->pajak_amount)}}</td>
			    </tr>
			<?php } ?>
			</table>
	  </div>
	  
	</div>
	<!-- /.tab-content -->
</div>
