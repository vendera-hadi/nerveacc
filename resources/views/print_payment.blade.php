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
   
    <div class="page">
        <div class="subpage">

            <table width="100%">
                <tr>
                    <td width="30%" style="vertical-align:top">
                        <img src="{{asset($company_logo)}}" style="width:110px;"/>
                        <br><br>
                        <div style="width:100%; height:110px; border:1px solid">
                            <p style="padding-left:10px; padding-right:10px">Diterima dari:<br><br>
                                {{$tenan}} / {{$unit}}</p>
                        </div>
                    </td>
                    <td width="40%" style="vertical-align:top"><center><h1>KWITANSI</h1></center></td>
                    <td width="30%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Nomor Kwitansi</td>
                                <td>: {{$header->no_kwitansi}}</td>
                            </tr>
                            <tr>
                                <td width="50%">Tanggal</td>
                                <td>: {{date('d M Y',strtotime($header->created_at))}}</td>
                            </tr>
                            
                        </table>
                    </td>
                </tr>
            </table>

            <br><br>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    <td width="80%" style="border-collapse: collapse; border: solid 1px;"><b>KETERANGAN</b><br><i>Description</i></td>
                    <td colspan="2" width="20%" style="border-collapse: collapse; border: solid 1px;"><b>JUMLAH</b><br><i>Amount</i></td>
                </tr>

                <?php
                    if(count($details) > 0){
                        $total = 0;
                        foreach ($details as $key => $value) {
                            $total += $value->invpayd_amount;
                ?>
                <tr>
                    <td style="vertical-align: top; padding-left:15px; padding-right:10px; padding-top:10px">
                        Invoice {{$value->inv_number}}
                        @if(count($value->details) > 0)
                        @foreach($value->details as $val)
                        <br>&nbsp;&nbsp;&nbsp;- {{$val}}
                        @endforeach
                        @endif
                    </td>
                    <td style="border-left: solid 1px; text-align:right; padding-top:10px">Rp.</td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px; padding-top:10px">
                        <div style="padding-right: 3px;">{{ number_format($value->invpayd_amount) }}</div>
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
                <tr>
                    <td >&nbsp;</td>
                    <td colspan="2" style="border-collapse: collapse; border-left: solid 1px;">&nbsp;</td>
                </tr>
                <tr style="border-top: 1px solid black;">
                    <td style="padding-left:15px; padding-right:10px; padding-bottom:10px; padding-top:10px"><b>TOTAL</b></td>
                    <td style="border-left: solid 1px; text-align:right;"><b>Rp.</b></td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px"><b>{{number_format($total,0)}}</b></td>
                </tr>
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td width="15%">Terbilang :</td>
                    <td width="85%" style="padding:5px; border:1px solid">{{$terbilang}}</td>
                </tr>
            </table>
            <br>
            <table width="100%" style="line-height: 18px;">
                <tr>
                    <td width="77%" style="vertical-align:top">
                        <p style="padding-right: 50px; font-size:10px">
                            Lembar 1 dipegang oleh Pemilik/Penghuni<br>
                            Lembar 2 dipegang Finance & Accounting<br>
                            Lembar 3 dipegang sebagai file/arsip
                        </p>
                    </td>
                    <td width="23%" style="text-align: center; vertical-align: top;">
                        Jakarta, <?php echo date('d M Y'); ?><br><br>
                        
                        <br><br><br>
                        <br><br>
                        <div style="width:100%; border:1px solid black"></div>
                    </td> 
                </tr>
                
            </table>

        </div>
    </div>
</body>

    <script type="text/javascript">
        window.print();
    </script>

</html>