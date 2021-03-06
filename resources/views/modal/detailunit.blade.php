<div class="row">
	<div class="col-sm-6">
		<table>
			<tr>
				<td width="120"><strong>Unit Code</strong></td><td width="15">:</td><td>{{$unit->unit_code}}</td>
			</tr>
			<tr>
				<td><strong>Unit Area</strong></td><td width="15">:</td><td>{{$unit->unit_sqrt}}</td>
			</tr>
			<tr>
				<td><strong>No VA Utilities</strong></td><td width="15">:</td><td>{{$unit->va_utilities}}</td>
			</tr>
      <tr>
        <td><strong>No VA Maintenance</strong></td><td width="15">:</td><td>{{$unit->va_maintenance}}</td>
      </tr>
			<tr>
				<td><strong>Floor</strong></td><td width="15">:</td><td>{{$unit->MsFloor->floor_name}}</td>
			</tr>
			<tr>
				<td><strong>Unit Type</strong></td><td width="15">:</td><td>{{$unit->UnitType->untype_name}}</td>
			</tr>
			<tr>
				<td><strong>No Meteran Air</strong></td><td width="15">:</td><td>{{$unit->meter_air}}</td>
			</tr>
			<tr>
				<td><strong>No Meteran Listrik</strong></td><td width="15">:</td><td>{{$unit->meter_listrik}}</td>
			</tr>

		</table>
	</div>
	<div class="col-sm-6">
		<table>
			@if(!empty($tenant))
			<tr>
				<td width="120"><strong>Owner Name</strong></td><td width="15">:</td><td>{{$tenant->tenan_name}}</td>
			</tr>
      @if(!empty($unitowner))
      <tr>
        <td width="120"><strong>Owned Since</strong></td><td width="15">:</td><td>{{date('d F Y', strtotime($unitowner->unitow_start_date))}}</td>
      </tr>
      @endif
			<tr>
				<td><strong>KTP</strong></td><td width="15">:</td><td>{{$tenant->tenan_idno}}</td>
			</tr>
			<tr>
				<td><strong>Phone</strong></td><td width="15">:</td><td>{{$tenant->tenan_phone}}</td>
			</tr>
			<tr>
				<td><strong>Fax</strong></td><td width="15">:</td><td>{{$tenant->tenan_fax}}</td>
			</tr>
			<tr>
				<td><strong>Address</strong></td><td width="15">:</td><td>{{$tenant->tenan_address}}</td>
			</tr>
			<tr>
				<td><strong>Email</strong></td><td width="15">:</td><td>{{$tenant->tenan_email}}</td>
			</tr>
			<tr>
				<td><strong>NPWP</strong></td><td width="15">:</td><td>{{$tenant->tenan_npwp}}</td>
			</tr>
			<tr>
				<td><strong>Tax Name</strong></td><td width="15">:</td><td>{{$tenant->tenan_taxname}}</td>
			</tr>
			<tr>
				<td><strong>Tax Address</strong></td><td width="15">:</td><td>{{$tenant->tenan_tax_address}}</td>
			</tr>
			<tr>
				<td><strong>PPN</strong></td><td width="15">:</td><td>@if(!empty($tenant->tenan_isppn)){{'yes'}}@else{{'no'}}@endif</td>
			</tr>
			<tr>
				<td><strong>PKP</strong></td><td width="15">:</td><td>@if(!empty($tenant->tenan_ispkp)){{'yes'}}@else{{'no'}}@endif</td>
			</tr>
			@else
			<tr>
				<td width="120"><strong>Owner</strong></td><td width="15">:</td><td>{{'-'}}</td>
			</tr>
			@endif
		</table>
	</div>
</div>
<div class="row" style="margin-top:40px">
    <div class="col-sm-12">
    	<h3>Renter / Penyewa</h3>
    	<div class="table-responsive">
        <table style="80%" class="table table-bordered" >
            <tr class="text-center">
              <th>Name</th>
              <th>KTP</th>
              <th>Phone</th>
              <th>Fax</th>
              <th>Email</th>
              <th>Address</th>
              <th>NPWP</th>
              <th>Start</th>
              <th>End</th>
              <th>Status</th>
            </tr>
            @if(count($renter) > 0)
            @foreach($renter as $rt)
            <tr>
              <td>{{$rt->MsTenantWT->tenan_name}}</td>
              <td>{{$rt->MsTenantWT->tenan_idno}}</td>
              <td>{{$rt->MsTenantWT->tenan_phone}}</td>
              <td>{{$rt->MsTenantWT->tenan_fax}}</td>
              <td>{{$rt->MsTenantWT->tenan_email}}</td>
              <td>{{$rt->MsTenantWT->tenan_address}}</td>
              <td>{{$rt->MsTenantWT->tenan_npwp}}</td>
              <td>{{date('d F Y', strtotime($rt->contr_startdate))}}</td>
              <td>{{date('d F Y', strtotime($rt->contr_enddate))}}</td>
              <td>{{date('d F Y', strtotime($rt->contr_status))}}</td>
            </tr>
            @endforeach
            @else
            <tr class="text-center">
              <td colspan="10">No renter</th>
            </tr>
            @endif
        </table>
    	</div>
    </div>
</div>

<div class="row" style="margin-top:40px">
    <div class="col-sm-12">
      <h3>History Pemilik</h3>
      <div class="table-responsive">
        <table style="80%" class="table table-bordered" >
            <tr class="text-center">
              <th>Name</th>
              <th>KTP</th>
              <th>Phone</th>
              <th>Fax</th>
              <th>Email</th>
              <th>Address</th>
              <th>NPWP</th>
              <th>Owned since</th>
            </tr>
            @if(count($prevowner) > 0)
            @foreach($prevowner as $pown)
            <tr>
              <td>{{$pown->tenantWT->tenan_name}}</td>
              <td>{{$pown->tenantWT->tenan_idno}}</td>
              <td>{{$pown->tenantWT->tenan_phone}}</td>
              <td>{{$pown->tenantWT->tenan_fax}}</td>
              <td>{{$pown->tenantWT->tenan_email}}</td>
              <td>{{$pown->tenantWT->tenan_address}}</td>
              <td>{{$pown->tenantWT->tenan_npwp}}</td>
              <td>{{date('d F Y', strtotime($pown->unitow_start_date))}}</td>
            </tr>
            @endforeach
            @else
            <tr class="text-center">
              <td colspan="8">No previous owner</th>
            </tr>
            @endif
        </table>
      </div>
    </div>
</div>