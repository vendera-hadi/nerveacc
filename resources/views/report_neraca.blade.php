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
			<div class="col-md-12">
				<center>
					<h4>
						{{$company->comp_name}}<br>
						<b>{{Request::get('title')}}</b><br>
						{{$datetxt}}<br>
						Dalam Rupiah (Rp)
					</h4>
				</center>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<table class="table table-stripped table-condensed">
					<?php for($i=0; $i<$total; $i++){ ?>
					<tr>
						<?php 
							if(isset($detail1[$i])){
								if($detail1[$i]->hide != '0'){
									$desc = str_replace(' ','&nbsp;',$detail1[$i]->desc);
									if(!empty($detail1[$i]->header)) $desc = '<b>'.$desc.'</b>';
									echo '<td>'.$desc.'</td>'; 
								}else{
									echo '<td>&nbsp;</td>';
								}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
						
							if(isset($detail1[$i])){

								$detail1[$i]->setDate($from, $to);
								$detail1[$i]->setVariables($variables);
								$calculate = $detail1[$i]->calculateAccount();
								if(!empty($detail1[$i]->variable)) $variables[$detail1[$i]->variable] = $calculate;

								if($detail1[$i]->hide != '0'){
									if(!empty($detail1[$i]->coa_code) || !empty($detail1[$i]->formula)){
										if(trim($detail1[$i]->underline) == '1'){
											echo '<td style="border-bottom: 1px solid black; text-align:right;">'.format_report_numeric($calculate).'</td>'; 
										}else{
											echo '<td style="text-align:right">'.format_report_numeric($calculate).'</td>';
										}
									}else{
										echo '<td>&nbsp;</td>';
									}
								}else{
										echo '<td>&nbsp;</td>';
									}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
							
							if(isset($detail2[$i])){
								if($detail2[$i]->hide != '0'){
									$desc = str_replace(' ','&nbsp;',$detail2[$i]->desc); 
									if(!empty($detail2[$i]->header)) $desc = '<b>'.$desc.'</b>';
									echo '<td>'.$desc.'</td>'; 
								}else{
									echo '<td>&nbsp;</td>';
								}
							}else{ 
								echo '<td>&nbsp;</td>';
							}
						
							if(isset($detail2[$i])){

								$detail2[$i]->setDate($from, $to);
								$detail2[$i]->setVariables($variables);
								$calculate = $detail2[$i]->calculateAccount();
								if(!empty($detail2[$i]->variable)) $variables[$detail2[$i]->variable] = $calculate;

								if($detail2[$i]->hide != '0'){
									if(!empty($detail2[$i]->coa_code) || !empty($detail2[$i]->formula)){
										if(trim($detail2[$i]->underline) == '1'){
											echo '<td style="border-bottom: 1px solid black; text-align:right;">'.format_report_numeric($calculate).'</td>'; 
										}else{
											echo '<td style="text-align:right">'.format_report_numeric($calculate).'</td>';
										}
									}else{
										echo '<td>&nbsp;</td>';
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
			</div>
		</div>
	</div>
</body>
</html>