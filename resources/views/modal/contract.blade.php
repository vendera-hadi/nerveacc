<div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#modaltab_1" data-toggle="tab">Information</a></li>
              <li><a href="#modaltab_2" data-toggle="tab">Cost Detail</a></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="modaltab_1">
                	<!-- information -->
                	<table width="100%">
					<tr><td width="40%"><strong>Contract Code</strong></td><td>:</td><td>{{$fetch['contr_code']}}</td></tr>
					<tr><td><strong>Contract Number</strong></td><td>:</td><td>{{$fetch['contr_no']}}</td></tr>
					<tr><td><strong>Contract Start Date</strong></td><td>:</td><td>{{!empty($fetch['contr_startdate']) ? date('d-m-Y', strtotime($fetch['contr_startdate'])) : '-'}}</td></tr>
					<tr><td><strong>Contract End Date</strong></td><td>:</td><td>{{!empty($fetch['contr_enddate']) ? date('d-m-Y', strtotime($fetch['contr_enddate'])) : '-'}}</td></tr>
					<tr><td><strong>Bukti Acara Serah Terima Date</strong></td><td>:</td><td>{{!empty($fetch['contr_bast_date']) ? date('d-m-Y', strtotime($fetch['contr_bast_date'])) : '-'}}</td></tr>
					<tr><td><strong>Bukti Acara Serah Terima By</strong></td><td>:</td><td>{{$fetch['contr_bast_by']}}</td></tr>
					<tr><td><strong>Note</strong></td><td>:</td><td>{{$fetch['contr_note']}}</td></tr>
					<tr><td><strong>Tenant Code</strong></td><td>:</td><td>{{$fetch['tenan_code']}}</td></tr>
					<tr><td><strong>Tenant Name</strong></td><td>:</td><td>{{$fetch['tenan_name']}}</td></tr>
					<tr><td><strong>Tenant Id No</strong></td><td>:</td><td>{{$fetch['tenan_idno']}}</td></tr>
					<tr><td><strong>Agent Code</strong></td><td>:</td><td>{{$fetch['mark_code']}}</td></tr>
					<tr><td><strong>Agent Name</strong></td><td>:</td><td>{{$fetch['mark_name']}}</td></tr>
					
					<tr><td><strong>Virtual Account No</strong></td><td>:</td><td>{{$fetch['viracc_no']}}</td></tr>
					<tr><td><strong>Virtual Account Name</strong></td><td>:</td><td>{{$fetch['viracc_name']}}</td></tr>
					<tr><td><strong>VA Active Status</strong></td><td>:</td><td>{{!empty($fetch['viracc_isactive']) ? 'active' : 'not active' }}</td></tr>
					<tr><td><strong>Contract Status Code</strong></td><td>:</td><td>{{$fetch['const_code']}}</td></tr>
					<tr><td><strong>Contract Status Name</strong></td><td>:</td><td>{{$fetch['const_name']}}</td></tr>
					<tr><td><strong>Unit Code</strong></td><td>:</td><td>{{$fetch['unit_code']}}</td></tr>
					<tr><td><strong>Unit Name</strong></td><td>:</td><td>{{$fetch['unit_name']}}</td></tr>
					<tr><td><strong>Unit Active Status</strong></td><td>:</td><td>{{!empty($fetch['unit_isactive']) ? 'active' : 'not active'}}</td></tr>
					</table>
                	<!-- end information -->
              </div>
              <!-- /.tab-pane -->
              <div class="tab-pane" id="modaltab_2">
                	@foreach($costdetail as $cdt)
                	<table width="100%">
					<tr><td width="40%"><strong>Invoice Type</strong></td><td>:</td><td>{{$cdt->invtp_name}}</td></tr>
					<tr><td><strong>Cost Name</strong></td><td>:</td><td>{{$cdt->costd_name}}</td></tr>
					<tr><td><strong>Biaya Rate</strong></td><td>:</td><td>{{$cdt->costd_rate}}</td></tr>
					<tr><td><strong>Biaya Burden</strong></td><td>:</td><td>{{$cdt->costd_burden}}</td></tr>
					<tr><td><strong>Biaya Admin</strong></td><td>:</td><td>{{$cdt->costd_admin}}</td></tr>
					<tr><td><strong>Use Meter</strong></td><td>:</td><td>@if($cdt->costd_ismeter){{'yes'}}@else{{'no'}}@endif</td></tr>
					</table>
					<br><br>
                	@endforeach
              </div>
              
            </div>
            <!-- /.tab-content -->
          </div>
