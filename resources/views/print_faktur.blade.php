<?php
		$company_name = $company['comp_name'];
		$no_invoice = $invoice_data['inv_number'];
		$invoice_date = date('d-m-Y', strtotime($invoice_data['inv_date']));
		$invoice_due_date = date('d-m-Y', strtotime($invoice_data['inv_duedate']));

		$tenan_name = $invoice_data['ms_tenant']['tenan_taxname'];
		$tenan_address = $invoice_data['ms_tenant']['tenan_tax_address'];

		$bank_name = $company['ms_cashbank']['cashbk_name'];
?>
<!DOCTYPE html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<head>
		<title>Cetak Faktur</title>
		<style>
			html,body { height:100% ;}
			div#container { height:100%; }
			div.left { height:100%; }

			.body-all{
				margin-left: 30px;
				border-left-width: 1px;
				border-left-style: dashed;
				padding-left: 15px;
				font-size: 14px;
			}
			.container{
				border:1px solid #000;
				padding: 5px 0px;
			}
			table {
			    border-collapse: collapse;
			    width: 100%;
			}
			.no-border{
				border: none;
			}
			.no-invoice{
				margin-bottom: 10px;
				font-size: 15px;
			}
			.bolder-text{
				font-weight: bold;
			}
			.section3 label{
				width: 78px;
				display: inline-block;
			}
			.border-top-bottom{
				border-top-color:#fff; 
				border-bottom-color:#fff; 
			}
			.inc-table{
				margin-top: -1px;
			}
			.border-ts-bh{
				border-top: 1px solid #000;
    			border-bottom: 1px solid #fff;
			}
			.full-border{
				border:1px solid #000;
			}
			.border-lft{
				border-left: 1px solid #fff;
			}
			.full-border{
				border:1px solid #000;
			}
			.border-full{
				border:1px solid #ccc;
			}
			.stamp-stamp{
				padding: 5px;
    			font-size: 13px;
			}
			.padd20{
				padding: 20px;
			}
			.notice-info{
				margin: 0px 0px 10px 30px;
			}
			.mt30{
				margin-top: 30px;
			}
			.pad-left{
				padding-left: 20px;
			}
			.content-list{
				padding: 10px 30px;
			}
		</style>
	</head>
	<body>
		<div class="body-all">
			<table border="1">
				<tr>
					<td class="no-border" colspan="3">
						<div class="header1" style="padding: 10px 0px 0px 10%;">
							<?php echo $company_name;?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="no-border" width="10%">
						&nbsp;
					</td>
					<td class="no-border" width="50%" align="center">
						<h1>FAKTUR</h1>
					</td>
					<td class="no-border" width="40%">
						<div class="section3">
							<div class="no-invoice">
								<?php
										echo '<label class="bolder-text">No.</label> <span>'.$no_invoice.'</span>';
								?>
							</div>
							<div class="date-invoice">
								<?php
										echo '<label>Tgl: </label> <span>'.$invoice_date.'</span><br>';
										echo '<label>Due Date: </label> <span>'.$invoice_due_date.'</span>';
								?>
							</div>
						</div>
							
					</td>
				</tr>
				<tr>
					<td class="no-border" width="15%" align="right">Kepada Yth:</td>
					<td class="no-border" width="85%" colspan="2">
						<div class="tenant-name">
							<?php
									echo $tenan_name;
							?>
						</div>
					</td>
				</tr>
				<tr>
					<td class="no-border" width="15%" align="right">&nbsp;</td>
					<td class="no-border" width="85%" colspan="2" valign="top">
						<table class="no-border">
							<tr>
								<td valign="top">
									<?php
										echo $tenan_address;
									?>
								</td>
								<td>
									<?php
										echo $company_name.'<br>';
										echo $bank_name.'<br>';
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table border="1" class="inc-table">
				<tr>
					<td class="no-border" colspan="2" style="padding: 0px;">
						<table border="1" width="100%" style="margin: -1px;">
							<tr>
								<td width="70%" align="center">
									Keterangan
								</td>
								<td width="30%" align="center">
									Jumlah
								</td>
							</tr>
							<?php
									if(!empty($result)){
										$total = 0;
										foreach ($result as $key => $value) {
											$total += $value['invdt_amount'];
							?>
							<tr>
								<td class="border-top-bottom content-list">
									<?php echo $value['invdt_note'];?>	
								</td>
								<td class="border-top-bottom content-list" align="right">
									<?php echo '<label>Rp.</label> <span>'.number_format($value['invdt_amount']).'</span>';?>
								</td>
							</tr>
							<?php
										}
							?>
							<tr>
								<td class="border-ts-bh border-lft content-list" align="right">Jumlah : </td>
								<td class="full-border content-list" align="right">
									<?php echo '<label>Rp.</label> <span>'.number_format($total).'</span>';?>
								</td>
							</tr>
							<?php
									}
							?>
						</table>
					</td>
				</tr>
				<tr>
					<td class="no-border" width="70%">
						<ul>
							<li>
								ini merupakan penagihan,<br>
								bukan merupakan bukti pembayaran.
							</li>
						</ul>
						<div class="padd20">
							<div class="stamp-stamp border-full">
								PEMBAYARAN MELALUI KASIR / TRANSFER DENGAN MENCANTUMKAN <br>
								NO INVOICE / UNIT ATAU KONFIRMASI VIA EMAIL / FAX
							</div>
						</div>
						<div class="notice-info">2 TRADE REC</div>
					</td>
					<td class="no-border" width="30%">
						Hormat kami,
						<br> 
						<br> 
						<br> 
						<br> 
						<br>
						(<?php echo $company['comp_sign_inv_name'];?>)
					</td>
				</tr>
			</table>
			<table class="mt30" border="1">
				<tr>
					<td style="border-bottom-color: #fff;">
						<table border="0">
							<tr>
								<td colspan="4" align="center"><h1><u>TANDA TERIMA FAKTUR</u></h1></td>
							</tr>
							<tr>
								<td class="no-border pad-left" width="5%">Kepada</td>
								<td class="no-border" align="center" width="10%">:</td>
								<td class="no-border" colspan="2">
									<?php 
										echo $tenan_name.'<br>';
										echo $tenan_address;
								?>
								</td>
							</tr>
							<tr>
								<td class="no-border pad-left" width="5%">Dari</td>
								<td class="no-border" align="center" width="10%">:</td>
								<td class="no-border" colspan="2">
									<?php 
										echo $company_name;
								?>
								</td>
							</tr>
							<tr>
								<td class="no-border pad-left" width="5%">Tanggal</td>
								<td class="no-border" align="center" width="10%">:</td>
								<td class="no-border" colspan="2">
									<?php 
										echo $invoice_date;
										echo '&nbsp;&nbsp;&nbsp;&nbsp;<label>Due Date : </label> &nbsp;&nbsp;'.$invoice_due_date;
								?>
								</td>
							</tr>
							<tr>
								<td class="no-border pad-left" width="5%">Keterangan</td>
								<td class="no-border" align="center" width="10%">:</td>
								<td class="no-border" colspan="2">
									Faktur No. &nbsp;&nbsp;&nbsp;&nbsp;
									<?php 
											echo $no_invoice;
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="no-border">&nbsp;</td>
				</tr>
				<tr>
					<td class="no-border">&nbsp;</td>
				</tr>
				<tr>
					<td class="no-border">&nbsp;</td>
				</tr>
				<tr>
					<td class="no-border">&nbsp;</td>
				</tr>
				<tr>
					<td class="no-border">
						<table>
							<tr>
								<td align="center">
									Diterima Oleh :<br>
									Tanggal : 
									<br>
									<br>
									<br>
									<br>
									<br>
									<u>(.......................................)</u><br>
									Nama Jelas
								</div>
								<td <td colspan="2" align="center">
									Diserahkan Oleh : <br>
									<br>
									<br>
									<br>
									<br>
									<br>
									<u>(.......................................)</u><br>
									Nama Jelas
								</div>
								<div class="clear"></div>
							</tr>
						</table>
					</td>
				</tr>
			</table>
						
						
		</div>
	</body>
	<?php
			if($type != 'pdf'){
	?>
	<script type="text/javascript">
		window.print();
	</script>
	<?php
			}
	?>
</html>
	