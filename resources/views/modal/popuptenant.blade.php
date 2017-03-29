<center><h3>Choose Tenant</h3></center><br>

<form action="post" id="@if($edit){{'searchTenantEdit'}}@else{{'searchTenant'}}@endif">
<div class="col-xs-6 col-xs-offset-6">
	<div class="input-group">
      <input type="text" name="keyword" class="form-control" placeholder="Search Tenant" value="@if(!empty($keyword)){{$keyword}}@endif">
      <span class="input-group-btn">
        <button class="btn btn-info" type="submit">Search</button>
      </span>
    </div><!-- /input-group -->
</div>
</form>
<br><br><br>

<table class="table table-bordered">
	<tr>
		<th></th>
		<th>Tenant Code</th>
		<th>Tenant Name</th>
		<th>Tenant Phone</th>
		<th>Tenant Email</th>
	</tr>
	@foreach($tenants as $tenant)
	<tr>
		<td><center><input type="radio" name="@if($edit){{'tenantedit'}}@else{{'tenant'}}@endif" data-name="{{$tenant->tenan_name}}" data-owned="{{($tenant->tent_id == 1) ? 1 : 0}}"  value="{{$tenant->id}}"></center></td>
		<td>{{$tenant->tenan_code}}</td>
		<td>{{$tenant->tenan_name}}</td>
		<td>{{$tenant->tenan_phone}}</td>
		<td>{{$tenant->tenan_email}}</td>
	</tr>
	@endforeach
	@if(count($tenants) > 0)
	<tr>
		<td colspan="5">
			<center><button type="button" class="btn btn-info" id="@if($edit){{'chooseTenantEdit'}}@else{{'chooseTenant'}}@endif">Choose</button></center>
		</td>
	</tr>
	@endif
</table>

<center>{{$tenants->appends(['keyword' => $keyword])->links()}}</center>