<!DOCTYPE html>
<html>
<head>
	<title>Preview</title>
	<link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/AdminLTE.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/skins/_all-skins.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/my_style.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col-md-12"><center><h3>{{$header->name}}</h3></center></div>
			@if($header->type==2)
				<table class="table table-bordered table-condensed">
					<?php for($i=0; $i<$total; $i++){ ?>
					<tr>
						<?php 
							if(isset($detail1[$i])){
								if(trim($detail1[$i]['hide']) == '0'){
									$desc = str_replace(' ','&nbsp;',$detail1[$i]['desc']); 
									if(!empty($detail1[$i]['header'])) $desc = '<b>'.$desc.'</b>';
									echo '<td>'.$desc.'</td>'; 
								}else{
									echo '<td>&nbsp;</td>';
								}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
						
							if(isset($detail1[$i])){
								if(trim($detail1[$i]['hide']) == '0'){
									if(trim($detail1[$i]['underline']) == '1'){
										echo '<td style="border-bottom: 1px solid black; text-align:center;">XXXXX</td>'; 
									}else{
										echo '<td style="text-align:center">XXXXX</td>';
									}
								}else{
										echo '<td>&nbsp;</td>';
									}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
							
							if(isset($detail2[$i])){
								if(trim($detail2[$i]['hide']) == '0'){
									$desc = str_replace(' ','&nbsp;',$detail2[$i]['desc']); 
									if(!empty($detail2[$i]['header'])) $desc = '<b>'.$desc.'</b>';
									echo '<td>'.$desc.'</td>'; 
								}else{
									echo '<td>&nbsp;</td>';
								}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
						
							if(isset($detail2[$i])){
								if(trim($detail2[$i]['hide']) == '0'){
									if(trim($detail2[$i]['underline']) == '1'){
										echo '<td style="border-bottom: 1px solid black; text-align:center;">XXXXX</td>'; 
									}else{
										echo '<td style="text-align:center">XXXXX</td>';
									}
								}else{
										echo '<td>&nbsp;</td>';
									}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
							?>
					</tr>
					<?php } ?>
				</table>
			@else
				<table class="table table-bordered table-condensed">
					<?php for($i=0; $i<$total; $i++){ ?>
					<tr>
						<?php 
							if(isset($detail1[$i])){
								if(trim($detail1[$i]['hide']) == '0'){
									$desc = str_replace(' ','&nbsp;',$detail1[$i]['desc']); 
									if(!empty($detail1[$i]['header'])) $desc = '<b>'.$desc.'</b>';
									echo '<td>'.$desc.'</td>'; 
								}else{
									echo '<td>&nbsp;</td>';
								}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
						
							if(isset($detail1[$i])){
								if(trim($detail1[$i]['hide']) == '0'){
									if(trim($detail1[$i]['underline']) == '1'){
										echo '<td style="border-bottom: 1px solid black; text-align:center;">XXXXX</td>'; 
									}else{
										echo '<td style="text-align:center">XXXXX</td>';
									}
								}else{
										echo '<td>&nbsp;</td>';
									}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
						?>
					</tr>
					<?php
						if(isset($detail1[$i])){
							if(trim($detail1[$i]['linespace']) != '0'){
								for($j=0; $j < $detail1[$i]['linespace']; $j++){
									echo '<tr><td colspan="2">&nbsp;</td></tr>';
								}
							}
						}
					 ?>
					<?php } ?>
				</table>
			@endif
		</div>
	</div>
</body>
</html>