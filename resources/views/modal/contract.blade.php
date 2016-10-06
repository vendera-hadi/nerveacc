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
<tr><td><strong>Rental Period Name</strong></td><td>:</td><td>{{$fetch['renprd_name']}}</td></tr>
<tr><td><strong>Rental Period Day</strong></td><td>:</td><td>{{$fetch['renprd_day']}}</td></tr>
<tr><td><strong>Virtual Account No</strong></td><td>:</td><td>{{$fetch['viracc_no']}}</td></tr>
<tr><td><strong>Virtual Account Name</strong></td><td>:</td><td>{{$fetch['viracc_name']}}</td></tr>
<tr><td><strong>VA Active Status</strong></td><td>:</td><td>{{!empty($fetch['viracc_isactive']) ? 'active' : 'not active' }}</td></tr>
<tr><td><strong>Contract Status Code</strong></td><td>:</td><td>{{$fetch['const_code']}}</td></tr>
<tr><td><strong>Contract Status Name</strong></td><td>:</td><td>{{$fetch['const_name']}}</td></tr>
<tr><td><strong>Unit Code</strong></td><td>:</td><td>{{$fetch['unit_code']}}</td></tr>
<tr><td><strong>Unit Name</strong></td><td>:</td><td>{{$fetch['unit_name']}}</td></tr>
<tr><td><strong>Unit Active Status</strong></td><td>:</td><td>{{!empty($fetch['unit_isactive']) ? 'active' : 'not active'}}</td></tr>
</table>