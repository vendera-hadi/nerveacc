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
  <title>PRINT DENDA</title>
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
    height: 240mm;
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
        height: 240mm;        
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

            <table width="100%">
                <tr>
                    <td width="70%" style="vertical-align:top">
                        <?php if($type == 'pdf' || $type == 'mail'){ ?>
                        <img src="{{'file://'.base_path('public/'.$company_logo)}}" style="width:110px;"/>
                        <?php }else{ ?>
                        <img src="{{asset($company_logo)}}" style="width:110px;"/>
                        <?php } ?>
                    </td>
                    <td width="30%">
                        <p style="font-size: 11pt;line-height: 1.8;">
                        Sudah Terima Dari :<br>
                        {{$tenan}}<br>
                        Unit:{{$unit}}
                        </p>
                    </td>
                </tr>
            </table>
            <table width="100%">
                <tr>
                    <td width="100%" style="font-weight: bold;text-align: center;font-size: 18pt;">TANDA TERIMA DENDA</td>
                </tr>
            </table>
            <table width="100%">
                <tr>
                    <td width="100%" style="text-align: center;font-size: 11pt;">No : {{$header->denda_number}}</td>
                </tr>
            </table>
            <br><br>
            <table width="100%">
                <tr>
                    <td width="40%" style="font-size: 11pt;">Banyaknya Uang :</td>
                    <td width="60%" style="padding:5px; border:1px solid;text-align: center;font-weight: bold;font-size: 11pt;">#{{strtoupper($terbilang)}}#</td>
                </tr>
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td width="40%" style="font-size: 11pt;">Untuk Pembayaran :</td>
                    <td width="60%" style="padding:5px;font-size: 11pt;">{{$header->denda_keterangan}}</td>
                </tr>
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td width="40%" style="border:1px solid;text-align: center;font-weight: bold;font-size: 11pt;">Jumlah Rp. {{number_format($header->denda_amount)}}</td>
                    <td width="60%">&nbsp;</td>
                </tr>
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td width="50%" style="vertical-align:top"></td>
                    <td width="50%" style="text-align: center;">
                        Tangerang, <?php echo date('d M Y',strtotime($header->denda_date)); ?>
                    </td> 
                </tr>
                <tr>
                    <td style="height:30px"><p style="font-size: 11pt;line-height: 1.8;">Pembayaran dengan Cheque/giro<br> di-anggap sah bila dananya telah<br> kami terima</p></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td style="text-align: center;">(&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;)</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><p style="font-size: 10pt;line-height: 1.8;text-align: center;">Managed by : PT Jakarta Land Management</p></td>
                </tr>
            </table>

        </div>
    </div>
</body>

    <script type="text/javascript">
        window.print();
    </script>

</html>