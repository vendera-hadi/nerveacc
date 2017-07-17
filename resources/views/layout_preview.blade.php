<html>
<head>
	<title>preview</title>
	<link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
	<center><h3>{{$header->name}}</h3></center><br><br>
	<div class="row">
	@if($detail1)
	<div class="@if($header->type==2){{'col-sm-6'}}@else{{'col-sm-12'}}@endif ">
	<table style="width:100%">
		@foreach($detail1 as $dt)
			@php
				$desc = str_replace(' ','&nbsp;',$dt->desc);
				if(!empty($dt->header)) $desc = '<b style="font-size:16px">'.$desc.'</b>';
			@endphp
			<tr>
				<td>{!!$desc!!}</b></td>
				<td style="text-align:right; @if(!empty($dt->underline)) border-bottom: 1px solid black @endif">@if(empty($dt->hide)) Rp. xxx @endif</td>
			</tr>
			@if(!empty($dt->linespace))
			@for($i=0; $i<count($dt->linespace); $i++)
			<tr>
				<td colspan="2" style="height:20px"></td>
			</tr>
			@endfor
			@endif
		@endforeach
	</table>
	</div>
	@endif

	@if($detail2)
	<div class="col-sm-6">
	<table style="width:100%">
		@foreach($detail2 as $dt)
			@php
				$desc = str_replace(' ','&nbsp;',$dt->desc);
				if(!empty($dt->header)) $desc = '<b style="font-size:16px">'.$desc.'</b>';
			@endphp
			<tr>
				<td>{!!$desc!!}</b></td>
				<td style="text-align:right; @if(!empty($dt->underline)) border-bottom: 1px solid black @endif">@if(empty($dt->hide)) Rp. xxx @endif</td>
			</tr>
			@if(!empty($dt->linespace))
			@for($i=0; $i<count($dt->linespace); $i++)
			<tr>
				<td colspan="2" style="height:20px"></td>
			</tr>
			@endfor
			@endif
		@endforeach
	</table>
	</div>
	@endif
	</div>
</body>
</html>