<?php
    $company_name = $company['comp_name'];
    $company_logo = $company['comp_image'];
    $company_address = $company['comp_address'];
    $company_phone = $company['comp_phone'];
    $company_fax = $company['comp_fax'];
    $company_sign = $company['comp_sign_inv_name'];
    $company_position = $company['comp_sign_position'];
    $no_invoice = $invoice_data['inv_number'];
    $invoice_date = date('d/m/Y', strtotime($invoice_data['inv_date']));
    $invoice_due_date = date('d/m/Y', strtotime($invoice_data['inv_duedate']));
    $tenan_name = $invoice_data['ms_tenant']['tenan_taxname'];
    $tenan_address = $invoice_data['ms_tenant']['tenan_tax_address'];
    $bank_name = $company['ms_cashbank']['cashbk_name'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>PRINT INVOICE</title>
<style type="text/css">
body {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    /*background-color: #FAFAFA;*/
    font: 11pt "Tahoma";
}
* {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
}
.page {
    width: 210mm;
    min-height: 277mm;
    padding: 10mm;
    margin: 10mm auto;
    border: 1px #D3D3D3 solid;
    border-radius: 5px;
    background: white;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}
.subpage {
    padding: 0cm;
    /*border: 1px #ccc solid;*/
    height: 250mm;
    outline: 1cm #fff solid;
    /*background: url("'.base_url().'asset/copied.png") no-repeat center;*/
    /*background-size: 80%;*/
}

@page {
    size: A4;
    margin: 0;
}
@media print {
    html, body {
        width: 210mm;
        height: 277mm;        
    }
    .page {
        margin: 0;
        border: initial;
        border-radius: initial;
        width: initial;
        min-height: initial;
        box-shadow: initial;
        background: initial;
        page-break-after: always;
    }
}
table,tr,hr,h1,h2,h3,h4,h5{margin:0;}
table tr td{font-size:9pt;}
</style>
</head>
<body>
    <div class="page">
        <div class="subpage">
            <div style="float:left;"><img src="{{asset('/upload/'.$company_logo)}}" style="width:110px;"/></div>
            <div style="font-size:10pt; font-weight: bold; text-align: center; width: 600px;"><?php echo $company_name; ?></div>
            <div style="font-size:10pt; text-align: center; width: 600px;"><?php echo $company_address; ?></div>
            <div style="font-size:10pt; text-align: center; width: 600px;"><?php echo 'Tlp: '.$company_phone.'/Fax: '.$company_fax; ?></div>
            <table style="width:100%;">
                <tr>
                    <td style="text-align:center;" colspan="2"><h4 style="font-size:12pt;">INVOICE</h4></td>
                </tr>
                <tr>
                    <td width="60%">No. Unit : <?php ?></td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Nomer / Number</td>
                                <td>: <?php echo $no_invoice; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="60%"><b><?php echo $tenan_name; ?></b></td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Tanggal / Date</td>
                                <td>: <?php echo $invoice_date; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td rowspan="2" width="60%" style="vertical-align: top;"><?php echo $tenan_address; ?></td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Jatuh Tempo</td>
                                <td>: <?php echo $invoice_due_date; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">No. Virtual Account</td>
                                <td>: </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    <td width="5%" style="border-collapse: collapse; border: solid 1px; font-weight: bold;">NO</td>
                    <td width="80%" style="border-collapse: collapse; border: solid 1px;"><b>KETERANGAN</b><br>Description</td>
                    <td width="15%" style="border-collapse: collapse; border: solid 1px;"><b>JUMLAH</b><br>Amount</td>
                </tr>
                <?php
                    if(!empty($result)){
                        $total = 0;
                        $no = 1;
                        foreach ($result as $key => $value) {
                            $total += $value['invdt_amount'];
                ?>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px; text-align: center; vertical-align: top;"><?php echo $no++; ?></td>
                    <td style="vertical-align: top;">
                        <?php echo $value['invdt_note'];?>
                    </td>
                    <td style="border-collapse: collapse; border-left: solid 1px; text-align: right;">
                        <div style="padding-right: 3px;">Rp. <?php echo number_format($value['invdt_amount']);?></div>
                    </td>
                </tr>
                <?php
                    }
                ?>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px; text-align: center; vertical-align: top;">4</td>
                    <td>BIAYA ADMINISTRASI</td>
                    <td style="border-collapse: collapse; border-left: solid 1px;"></td>
                </tr>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="border-collapse: collapse; border-left: solid 1px;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px;">&nbsp;</td>
                    <td>Tagihan Bulan Ini</td>
                    <td style="border-collapse: collapse; border-left: solid 1px;"></td>
                </tr>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px;">&nbsp;</td>
                    <td>Denda</td>
                    <td style="border-collapse: collapse; border-left: solid 1px;"></td>
                </tr>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px;">&nbsp;</td>
                    <td>Tagihan belum terbayar</td>
                    <td style="border-collapse: collapse; border-left: solid 1px;"></td>
                </tr>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="border-collapse: collapse; border-left: solid 1px;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="border-collapse: collapse; border-right: solid 1px;">&nbsp;</td>
                    <td><b>TOTAL</b></td>
                    <td style="border-collapse: collapse; border-left: solid 1px;"></td>
                </tr>
                <?php
                    }
                ?>
            </table>
            <br>
            <table width="100%" style="line-height: 18px;">
                <tr>
                    <td width="77%">Note:</td>
                    <td width="23%" style="text-align: center;">Jakarta, <?php echo date('d F Y'); ?></td>
                </tr>
                <tr>
                    <td>Pembayaran melalui <?php echo $bank_name; ?></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><span style="display:inline-block; width:30px;"></span>No Rekening ...</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td><span style="display:inline-block; width:30px;"></span>Atas Nama ...</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>KETENTUAN :</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>1. Pembayaran TRANSFER harap mencantumkan NO.INVOICE / NO.UNIT</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>2. Denda minimum dikenakan 1/1000 (per mil) dijumlahkan hari keterlambatan</td>
                    <td style="text-align: center; text-decoration: underline;font-weight: bold;"><?php echo $company_sign; ?></td>
                </tr>
                <tr>
                    <td>3. Pengelola akan memutuskan aliran listrik dan air apabila penghuni belum melunasi dalam jangka waktu 30 Hari.</td>
                    <td style="text-align: center; vertical-align: top;"><?php echo $company_position; ?></td>
                </tr>
            </table>
        </div>
    </div>

</body>
<!--
<?php if($type != 'pdf'){ ?>
    <script type="text/javascript">
        window.print();
    </script>
<?php } ?>
!-->
</html>