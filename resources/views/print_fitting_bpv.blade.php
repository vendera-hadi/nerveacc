<?php
    $company_name = $company['comp_name'];
    $company_logo = 'upload/'.$company['comp_image'];
    $company_address = $company['comp_address'];
    $company_phone = $company['comp_phone'];
    $company_fax = $company['comp_fax'];
    $company_sign = $company['comp_sign_inv_name'];
    $company_position = @$company['comp_sign_position'];
    $bank_name = $company['ms_cashbank']['cashbk_name'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>PRINT BPV</title>
<style type="text/css">
body {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    /*background-color: #FAFAFA;*/
    font: 10pt "Tahoma";
}
* {
    box-sizing: border-box;
    -moz-box-sizing: border-box;
}
.page {
    /*width: 210mm;*/
    /*min-height: 277mm;*/
    padding: 10mm;
    margin: 10mm auto;
    /*border: 1px #D3D3D3 solid;*/
    border-radius: 5px;
    background: white;
    /*box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);*/
}
.subpage {
    padding: 0cm;
    /*border: 1px #ccc solid;*/
    height: 170mm;
    /*outline: 1cm #fff solid;*/
    /*background: url("'.base_url().'asset/copied.png") no-repeat center;*/
    /*background-size: 80%;*/
}

@page {
    size: A4;
    margin: 0;
    size: landscape;
}
@media print {
    html, body {
        width: 297mm;
        height: 210mm;
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
    @foreach($ap_data as $inv)
    <?php
    $amount = $inv['out_amount'];
    $ap_date = date('d-M-Y', strtotime($inv['out_date']));
    $ptype = 'Cek';
    ?>
    <div class="page">
        <div class="subpage">
            <table style="width:100%">
                <tr>
                    <td>
                        <div style="font-size:12pt; text-align: center; width: 100%;">
                            <b>{{ 'PPRS '.$company_name }}</b><br>
                        </div>
                    </td>
                </tr>
            </table>
            
            <table style="width:100%;">
                <tr>
                    <td style="text-align:center;" colspan="2" height="50"><h4 style="font-size:12pt;">BANK PAYMENT VOUCHER (BPV)</h4></td>
                </tr>
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Paid To</td>
                                <td>: </td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Number</td>
                                <td>: </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Amount</td>
                                <td>: <?php echo '<strong>IDR '.number_format($amount).'</strong>'; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">BPV Date</td>
                                <td>: <?php echo $ap_date; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td width="50%">&nbsp;</td>
                                <td>&nbsp;&nbsp;<?php echo '<strong>'.$inv['terbilang'].'</strong>'; ?></td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Due Date</td>
                                <td>: </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td rowspan="2" width="60%" style="vertical-align: top;">&nbsp;</td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Payment method</td>
                                <td>: <b>{{$ptype}}</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Number</td>
                                <td>: </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    <td width="25%" style="border-collapse: collapse; border: solid 1px;"><b>No. / Account Name</b></td>
                    <td width="25%" style="border-collapse: collapse; border: solid 1px;"><b>Descriptions</b></td>
                    <td width="25%" style="border-collapse: collapse; border: solid 1px;"><b>&nbsp;</b></td>
                    <td width="25%" style="border-collapse: collapse; border: solid 1px;"><b>Amount</b></td>
                </tr>
                <tr>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                </tr>
                <?php
                    if(!empty($inv['details'])){
                        $total = 0;
                        foreach ($inv['details'] as $key => $value) {
                            $total += $value['ledg_credit'];
                ?>
                <tr>
                    <td style="padding-left:15px;border: solid 1px; vertical-align:center;">
                        <?php echo trim($value['coa_code']).' '.$lsc[trim($value['coa_code'])];?>
                    </td>
                    <td style="padding-left:15px;border: solid 1px; vertical-align:center;"><?php echo $value['ledg_description'];?></td>
                    <td style="border: solid 1px; vertical-align:center;">
                        <?php if($value['ledg_debit'] != 0){ ?>
                        <table width="100%">
                            <tr>
                                <td width="50%">Rp. </td>
                                <td style="text-align: right"><?php echo number_format($value['ledg_debit']);?></td>
                            </tr>
                        </table>
                        <?php } ?>   
                    </td>
                    <td style="border: solid 1px; vertical-align:center;">
                        <?php if($value['ledg_credit'] != 0){ ?>
                        <table width="100%">
                            <tr>
                                <td width="50%">Rp. </td>
                                <td style="text-align: right"><?php echo number_format($value['ledg_credit']);?></td>
                            </tr>
                        </table>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                    }
                ?>
                <tr>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px; text-align: right;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px; padding-right:10px; padding-top:10px; border: solid 1px;">&nbsp;</td>
                    <td style="padding-left:15px;border: solid 1px; vertical-align:center;"><b>Total</b></td>
                    <td style="border: solid 1px; vertical-align:center;">
                        <table width="100%">
                            <tr>
                                <td width="50%">Rp. </td>
                                <td style="text-align: right"><?php echo number_format($total);?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
                    }
                ?>
            </table>
            <br><br>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    <td width="20%" style="border-collapse: collapse; border: solid 1px;"><b>Request by,</b></td>
                    <td width="20%" style="border-collapse: collapse; border: solid 1px;"><b>Verified by,</b></td>
                    <td width="20%" style="border-collapse: collapse; border: solid 1px;" colspan="2"><b>Approved by,</b></td>
                    <td width="20%" style="border-collapse: collapse; border: solid 1px;"><b>Received by,</b></td>
                </tr>
                <tr>
                    <td style="border: solid 1px;height: 80px;">&nbsp;</td>
                    <td style="border: solid 1px;height: 80px;">&nbsp;</td>
                    <td style="border: solid 1px;height: 80px;">&nbsp;</td>
                    <td style="border: solid 1px;height: 80px;">&nbsp;</td>
                    <td style="border: solid 1px;height: 80px;">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
</body>

<?php if($type != 'pdf'){ ?>
    <script type="text/javascript">
        window.print();
    </script>
<?php } ?>

</html>