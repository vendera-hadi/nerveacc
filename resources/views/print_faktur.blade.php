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
    @foreach($invoice_data as $inv)
    <?php
    $no_invoice = $inv['inv_number'];
    $invoice_date = date('d/m/Y', strtotime($inv['inv_date']));
    $invoice_due_date = date('d/m/Y', strtotime($inv['inv_duedate']));
    $tenan_name = $inv['ms_tenant']['tenan_taxname'];
    $tenan_address = $inv['ms_tenant']['tenan_tax_address'];
    ?>
    <div class="page">
        <div class="subpage">
            <div style="position:absolute;"><img src="{{asset($company_logo)}}" style="width:110px;"/></div>
            <div style="font-size:10pt; text-align: center; width: 100%;">
                    <b>{{ $company_name }}</b><br>
                    {{ $company_address }}<br>
                    {{'Tlp: '.$company_phone.'/Fax: '.$company_fax}}<br><br><br>
            </div>

            <table style="width:100%;">
                <tr>
                    <td style="text-align:center;" colspan="2"><h4 style="font-size:12pt;">INVOICE</h4></td>
                </tr>
                <tr>
                    <td width="60%">Nama Tenant / No Unit :</td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Nomor / <i>Number</i></td>
                                <td>: <?php echo $no_invoice; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="60%"><b><?php echo $tenan_name; ?> / {{$inv['unit_code']}}</b></td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Tanggal / <i>Date</i></td>
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
                                <td width="50%">Jatuh Tempo / <i>Due Date</i></td>
                                <td>: <?php echo $invoice_due_date; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%"><i>Virtual Account</i></td>
                                <td>: {{$inv['virtual_account']}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <br>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    
                    <td width="80%" style="border-collapse: collapse; border: solid 1px;"><b>KETERANGAN</b><br><i>Description</i></td>
                    <td width="20%" colspan="2" style="border-collapse: collapse; border: solid 1px;"><b>JUMLAH</b><br><i>Amount</i></td>
                </tr>
                <?php
                    if(!empty($inv['details'])){
                        $total = 0;
                        $no = 1;
                        foreach ($inv['details'] as $key => $value) {
                            $total += $value['invdt_amount'];
                ?>
                <tr>
                    <td style="vertical-align: top; padding-left:15px; padding-right:10px; padding-top:10px">
                        <?php echo $value['invdt_note'];?>
                    </td>
                    <td style="border-left: solid 1px; text-align:right">Rp.</td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px">
                        <div style="padding-right: 3px;"><?php echo number_format($value['invdt_amount']);?></div>
                    </td>
                </tr>
                <?php
                    }
                ?>
                <tr>
                    
                    <td style="padding-left:15px; padding-right:10px">BIAYA ADMINISTRASI</td>
                    <td colspan="2" style="border-collapse: collapse; border-left: solid 1px;"></td>
                </tr>
                <tr>
                    
                    <td>&nbsp;</td>
                    <td colspan="2" style="border-collapse: collapse; border-left: solid 1px;">&nbsp;</td>
                </tr>
                <tr style="border-top: 1px solid black;">
                    
                    <td style="padding-left:15px; padding-right:10px; padding-bottom:10px; padding-top:10px"><b>TOTAL TAGIHAN BULAN INI</b></td>
                    <td style="border-left: solid 1px; text-align:right">Rp.</td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px"><b>{{number_format($total,0)}}</b></td>
                </tr>
                <?php
                    }
                ?>
            </table>
            <br>
            <div style="font-size: 9pt;">
            Terbilang :<br>
            {{$inv['terbilang']}}
            </div>
            <table width="100%" style="line-height: 18px;">
                <tr>
                    <td width="77%" style="vertical-align:top">
                        {!!$inv['footer']!!}<br><br>
                        {!!$inv['label']!!}
                    </td>
                    <td width="23%" style="text-align: center; vertical-align: top;">
                        Jakarta, <?php echo date('d M Y'); ?><br><br>
                        @if(!empty($signature) && !empty($signatureFlag))
                        <img src="{{asset($signature)}}" width="150">
                        <br><br>
                        <b><u>{{$company_sign}}</u></b><br>
                        {{$company_position}}
                        @else
                        <br><br><br><br><br>
                        <b><u>{{$company_sign}}</u></b><br>
                        {{$company_position}}
                        @endif
                        
                    </td>
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