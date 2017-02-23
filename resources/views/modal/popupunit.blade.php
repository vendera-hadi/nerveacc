<center><h3>Choose Unit</h3></center><br>

<form action="post" id="@if($edit){{'searchUnitEdit'}}@else{{'searchUnit'}}@endif">
<div class="col-xs-6 col-xs-offset-6">
	<div class="input-group">
      <input type="text" name="keyword" class="form-control" placeholder="Search Unit" value="@if(!empty($keyword)){{$keyword}}@endif">
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
		<th>Unit Code</th>
		<th>Unit Name</th>
		<th>Size</th>
	</tr>
	@foreach($units as $unit)
		<td><center><input type="radio" name="@if($edit){{'unitedit'}}@else{{'unit'}}@endif" data-name="{{$unit->unit_code}}" data-vaccount="{{$unit->virtual_account}}" value="{{$unit->id}}"></center></td>
		<td>{{$unit->unit_code}}</td>
		<td>{{$unit->unit_name}}</td>
		<td>{{(int)$unit->unit_sqrt." m2"}}</td>
	</tr>
	@endforeach
	@if(count($units) > 0)
	<tr>
		<td colspan="4">
			<center><button type="button" class="btn btn-info" id="@if($edit){{'chooseUnitEdit'}}@else{{'chooseUnit'}}@endif">Choose</button></center>
		</td>
	</tr>
	@endif
</table>

<center>{{$units->appends(['keyword' => $keyword])->links()}}</center>