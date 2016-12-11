<center><h3>Choose Contract</h3></center><br>

<form action="post" id="@if($edit){{'searchContractEdit'}}@else{{'searchContract'}}@endif">
<div class="col-xs-6 col-xs-offset-6">
	<div class="input-group">
      <input type="text" name="keyword" class="form-control" placeholder="Search Contract" value="@if(!empty($keyword)){{$keyword}}@endif">
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
		<th>Contract No</th>
		<th>Contract Code</th>
		<th>Unit Name</th>
		<th>Tenan Name</th>
	</tr>
	@foreach($contracts as $contract)
	<tr>
		<td><center><input type="radio" name="@if($edit){{'contractedit'}}@else{{'contract'}}@endif" data-name="{{$contract->contr_code}}" value="{{$contract->id}}"></center></td>
		<td>{{$contract->contr_no}}</td>
		<td>{{$contract->contr_name}}</td>
		<td>{{$contract->unit_name}}</td>
		<td>{{$contract->tenan_name}}</td>
	</tr>
	@endforeach
	@if(count($contracts) > 0)
	<tr>
		<td colspan="5">
			<center><button type="button" class="btn btn-info" id="@if($edit){{'chooseContractEdit'}}@else{{'chooseContract'}}@endif">Choose</button></center>
		</td>
	</tr>
	@endif
</table>

<center>{{$contracts->appends(['keyword' => $keyword])->links()}}</center>