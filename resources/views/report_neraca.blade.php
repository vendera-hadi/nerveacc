<center>
	<h4 style="font-size: 16px;">
		LAPORAN NERACA<br>
		{{$company->comp_name}}<br>
		{{$datetxt}}
	</h4>

	<table style="margin-top:60px">
		<tr>
			<td style="padding-right:15px; padding-top:15px; border-top: 1px solid black; border-right: 1px solid black;">
				<!-- kiri -->
				<table width="400px">
					<tr>
						<td colspan="2"><center><h3>AKTIVA</h3></center></td>
					</tr>
					@foreach($detail1 as $dt)
					@php
						$desc = str_replace(' ','&nbsp;',$dt->desc);
						$dt->setDate($from, $to);
						if(!empty($dt->header)) $desc = '<b style="font-size:16px">'.$desc.'</b>';
						$dt->setVariables($variables);
						$calculate = $dt->calculateAccount();
						if(!empty($dt->variable)) $variables[$dt->variable] = $calculate;
					@endphp
					@if(empty($dt->hide))
						<tr>
							<td>{!!$desc!!}</b></td>
							<td style="text-align:right; @if(!empty($dt->underline)) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ 'Rp. '.number_format($calculate, 0, ',', '.') }}@endif</td>
						</tr>
						@if(!empty($dt->linespace))
						@for($i=0; $i<count($dt->linespace); $i++)
						<tr>
							<td colspan="2" style="height:20px"></td>
						</tr>
						@endfor
						@endif
					@endif
					@endforeach
				</table>
				<!-- kiri -->
			</td>
			<td style="padding-left:15px; padding-top:15px; border-top: 1px solid black; ">
				<!-- kanan -->
				<table width="400px">
					<tr>
						<td colspan="2"><center><h3>PASIVA</h3></center></td>
					</tr>
					@foreach($detail2 as $dt)
					@php
						$desc = str_replace(' ','&nbsp;',$dt->desc);
						$dt->setDate($from, $to);
						if(!empty($dt->header)) $desc = '<b style="font-size:16px">'.$desc.'</b>';
						$dt->setVariables($variables);
						$calculate = $dt->calculateAccount();
						if(!empty($dt->variable)) $variables[$dt->variable] = $calculate;
					@endphp
					@if(empty($dt->hide))
						<tr>
							<td>{!!$desc!!}</b></td>
							<td style="text-align:right; @if(!empty($dt->underline)) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ 'Rp. '.number_format($calculate, 0, ',', '.') }}@endif</td>
						</tr>
						@if(!empty($dt->linespace))
						@for($i=0; $i<count($dt->linespace); $i++)
						<tr>
							<td colspan="2" style="height:20px"></td>
						</tr>
						@endfor
						@endif
					@endif
					@endforeach
				</table>
			</td>
		</tr>
	</table>
</center>