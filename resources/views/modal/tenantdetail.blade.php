<div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#modaltab_1" data-toggle="tab">Tenant Information</a></li>
              <li><a href="#modaltab_2" data-toggle="tab">@if(count($units) > 0){{ 'Unit Owned' }}@else{{ 'Rented Unit' }}@endif</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="modaltab_1">
	<!-- information -->
	<table width="100%">
	<tr><td width="40%"><strong>Code</strong></td><td>:</td><td>{{$fetch['tenan_code']}}</td></tr>
	<tr><td><strong>Name</strong></td><td>:</td><td>{{$fetch['tenan_name']}}</td></tr>
	<tr><td><strong>KTP No</strong></td><td>:</td><td>{{$fetch['tenan_idno']}}</td></tr>
	<tr><td><strong>Phone No</strong></td><td>:</td><td>{{$fetch['tenan_phone']}}</td></tr>
	<tr><td><strong>Fax</strong></td><td>:</td><td>{{$fetch['tenan_fax']}}</td></tr>
	<tr><td><strong>Email</strong></td><td>:</td><td>{{$fetch['tenan_email']}}</td></tr>
	<tr><td><strong>Address</strong></td><td>:</td><td>{{$fetch['tenan_address']}}</td></tr>
	<tr><td><strong>NPWP</strong></td><td>:</td><td>{{$fetch['tenan_npwp']}}</td></tr>
	<tr><td><strong>Tax Name</strong></td><td>:</td><td>{{$fetch['tenan_taxname']}}</td></tr>
	<tr><td><strong>Tax Address</strong></td><td>:</td><td>{{$fetch['tenan_tax_address']}}</td></tr>
	<tr><td><strong>PPN</strong></td><td>:</td><td>{{$fetch['tenan_isppn'] ? 'yes' : 'no'}}</td></tr>
	<tr><td><strong>Tenant Type</strong></td><td>:</td><td>{{$fetch['tent_name']}}</td></tr>

	</table>
	<!-- end information -->
	</div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="modaltab_2">
	@if(count($units) > 0)
	@foreach($units as $key => $unit)
	<table width="100%">
	<tr><td width="40%"><strong><u>Unit #{{$unit->unit_code}}</u></strong></td><td></td><td></td></tr>
	<tr><td><strong>Unit Name</strong></td><td>:</td><td>{{$unit['unit_name']}} ({{$unit['unit_sqrt']}} m2)</td></tr>
	<tr><td><strong>Virtual Account Utilities</strong></td><td>:</td><td>{{@$unit['va_utilities']}}</td></tr>
  <tr><td><strong>Virtual Account Maintenance</strong></td><td>:</td><td>{{@$unit['va_maintenance']}}</td></tr>
  @php
    $renter = App\Models\TrContract::where('unit_id',@$unit['unit_id'])->where('tenan_id','!=',$id)->first();
  @endphp
  <tr><td><strong>Renter</strong></td><td>:</td><td>{{@$renter->MsTenant->tenan_name}}</td></tr>
	<tr><td><a href="#" data-tenan="{{$id}}" data-unit="{{$unit['unit_id']}}" class="deleteUnit" title="Delete Tenant Unit"><i class="fa fa-trash" aria-hidden="true"></i> Delete this unit</a></td><td></td><td></td></tr>
	</table>
	<br><br>
	@endforeach
	@else
      @if(count($rented) > 0)
        @foreach($rented as $key => $unit)
        <table width="100%">
        <tr><td width="40%"><strong><u>Unit #{{$unit->unit_code}}</u></strong></td><td></td><td></td></tr>
        <tr><td><strong>Unit Name</strong></td><td>:</td><td>{{$unit['unit_name']}} ({{$unit['unit_sqrt']}} m2)</td></tr>
        <tr><td><strong>Virtual Account Utilities</strong></td><td>:</td><td>{{@$unit['va_utilities']}}</td></tr>
        <tr><td><strong>Virtual Account Maintenance</strong></td><td>:</td><td>{{@$unit['va_maintenance']}}</td></tr>
        @php
        $unitOwner = App\Models\MsUnitOwner::where('unit_id',@$unit['id'])->first();
        @endphp
        <tr><td><strong>Owner</strong></td><td>:</td><td>{{@$unitOwner->tenant->tenan_name}}</td></tr>
        </table>
        <br><br>
        @endforeach
      @else
        <h3>No property owned / rented by this tenant</h3>
      @endif
	@endif
			</div>

            </div>
            <!-- /.tab-content -->
          </div>
