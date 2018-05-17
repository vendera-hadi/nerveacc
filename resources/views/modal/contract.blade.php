<div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#modaltab_1" data-toggle="tab">Information</a></li>
              <li><a href="#modaltab_2" data-toggle="tab">Cost Detail</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="modaltab_1">
                	<!-- information -->
                	<table width="100%">
					<tr><td width="40%"><strong>Billing Info Code</strong></td><td>:</td><td>{{$fetch['contr_code']}}</td></tr>
					<tr><td><strong>Billing Info Number</strong></td><td>:</td><td>{{$fetch['contr_no']}}</td></tr>
					<tr><td><strong>Billing Info Start Date</strong></td><td>:</td><td>{{!empty($fetch['contr_startdate']) ? date('d-m-Y', strtotime($fetch['contr_startdate'])) : '-'}}</td></tr>
					<tr><td><strong>Billing Info End Date</strong></td><td>:</td><td>
          @if($fetch['contr_enddate'] == '2030-12-31'){{'-'}}@else{{!empty($fetch['contr_enddate']) ? date('d-m-Y', strtotime($fetch['contr_enddate'])) : '-'}}@endif</td></tr>
					<tr><td><strong>Bukti Acara Serah Terima Date</strong></td><td>:</td><td>{{!empty($fetch['contr_bast_date']) ? date('d-m-Y', strtotime($fetch['contr_bast_date'])) : '-'}}</td></tr>
					<tr><td><strong>Bukti Acara Serah Terima By</strong></td><td>:</td><td>{{$fetch['contr_bast_by']}}</td></tr>
					@if(!empty($fetch['contr_note']))<tr><td><strong>Rollback Note</strong></td><td>:</td><td>{{$fetch['contr_note']}}</td></tr>@endif
					<tr><td><strong>Tenant Code</strong></td><td>:</td><td>{{$fetch['tenan_code']}}</td></tr>
					<tr><td><strong>Tenant Name</strong></td><td>:</td><td>{{$fetch['tenan_name']}}</td></tr>
					<tr><td><strong>Tenant Id No</strong></td><td>:</td><td>{{$fetch['tenan_idno']}}</td></tr>

					<tr><td><strong>VA Utilities</strong></td><td>:</td><td>{{$fetch['va_utilities']}}</td></tr>
          <tr><td><strong>VA Maintenance</strong></td><td>:</td><td>{{$fetch['va_maintenance']}}</td></tr>
					<tr><td><strong>Billing Info Status</strong></td><td>:</td><td>{{$fetch['contr_status']}}</td></tr>
					<tr><td><strong>Unit Code</strong></td><td>:</td><td>{{$fetch['unit_code']}}</td></tr>
					<tr><td><strong>Unit Name</strong></td><td>:</td><td>{{$fetch['unit_name']}}</td></tr>
					<tr><td><strong>Unit Active Status</strong></td><td>:</td><td>{{!empty($fetch['unit_isactive']) ? 'active' : 'not active'}}</td></tr>
          <tr><td><strong>Created By</strong></td><td>:</td><td>{{@$fetch['creator']['name']}}</td></tr>
          <tr><td><strong>Updated By</strong></td><td>:</td><td>{{@$fetch['updater']['name']}}</td></tr>
					</table>
                	<!-- end information -->
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="modaltab_2">
                	@foreach($costdetail as $cdt)
                	<table width="100%">
					<tr><td width="40%"><strong>Invoice Type</strong></td><td>:</td><td>{{$cdt->invtp_name}}</td></tr>
					<tr><td><strong>Cost Name</strong></td><td>:</td><td>{{$cdt->costd_name}}</td></tr>
					<tr><td><strong>Biaya Rate</strong></td><td>:</td><td>{{"Rp. ".number_format($cdt->costd_rate,2)}}</td></tr>
					<tr><td><strong>Biaya Burden</strong></td><td>:</td><td>{{"Rp. ".number_format($cdt->costd_burden,2)}}</td></tr>
					<tr><td><strong>Biaya Admin</strong></td><td>:</td><td>{{"Rp. ".number_format($cdt->costd_admin,2)}}</td></tr>
					<tr><td><strong>Use Meter</strong></td><td>:</td><td>@if($cdt->costd_ismeter){{'yes'}}@else{{'no'}}@endif</td></tr>
					</table>
					<br><br>
                	@endforeach
              </div>

            </div>
            <!-- /.tab-content -->
          </div>
