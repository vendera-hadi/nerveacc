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
  <title>PRINT REMINDER OUTSTANDING</title>
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
    height: 240mm;
    /*outline: 1cm #fff solid;*/
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
        height: 297mm;
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
    $no_ref = $inv['reminder_no'];
    $rm_date = $inv['reminder_date'];
    $unit_code = $inv['unit_code'];
    $tenan_name = $inv['tenan_name'];
    $manual = $inv['manual_inv'];
    $type_sp = $inv['sp_type'];
    $isicontent = $inv['isi_content'];
    $judul = $inv['subject'];
    $last = ($inv['rdate'] != '' ? ' yang dikirimkan pada tanggal '.date('d-m-Y',strtotime($inv['rdate'])) : '');
    /*
    $no_invoice = $inv['inv_number'];
    $invoice_date = date('d/m/Y', strtotime($inv['inv_date']));
    $invoice_due_date = date('d/m/Y', strtotime($inv['inv_duedate']));
    $tenan_name = $inv['ms_tenant']['tenan_name'];
    $tenan_address = $inv['ms_tenant']['tenan_address'];
    $invoice_type_prefix = $inv['invoice_type']['invtp_prefix'];
    $virtual_account = ($invoice_type_prefix == 'MN ') ? $inv['va_maintenance'] : $inv['va_utilities'];
    */
    ?>
    <div class="page">
        <div class="subpage">
            <table style="width:100%">
                <tr>
                    <td style="width:120px;">
                        <?php if($type == 'pdf' || $type == 'mail'){ ?>
                        <img src="{{'file://'.base_path('public/'.$company_logo)}}" style="width:120px;"/>
                        <?php }else{ ?>
                        <img src="{{asset($company_logo)}}" style="width:120px;"/>
                        <?php } ?>
                    </td>
                    <td>
                        <div style="font-size:10pt; text-align: center; width: 100%;">
                            <b>{{ $company_name }}</b><br>
                            {!! $company_address !!}<br>
                            {{'Tlp: '.$company_phone.'/Fax: '.$company_fax}}<br><br><br>
                        </div>
                    </td>
                </tr>
            </table>
            
            <table style="width:100%;">
                <tr>
                    <td width="60%">
                        Tangerang Selatan, <?php echo date('d F Y',strtotime($rm_date)); ?>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td width="50%">Ref No</td>
                                <td>: {{$no_ref}}</td>
                            </tr>
                        </table>
                    </td> 
                </tr>
                <tr>
                    <td width="60%">
                        Kepada Yth,
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>  
                    <td width="60%">
                       Pemilik Unit <b>{{$unit_code}} / {{$tenan_name}}</b>
                    </td>
                    <td rowspan="2" width="40%" style="vertical-align: top;">&nbsp;</td>
                </tr>
                <tr>
                    <td width="60%">
                        {{$company_name}}
                    </td>
                </tr>
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td><center><h4>{{$judul}}</h4></center></td>
                </tr>
                <tr>
                    <td>
                        @if($type_sp == 4)
                        <p style="line-height: 1.6;text-align: justify;text-justify: inter-word;">Dengan hormat, <br>
                            Perkenankan kami, Manajemen {{$company_name}}, mengucapkan terima kasih kepada Bapak/Ibu atas kerjasama yang telah terjalin dengan baik selama ini dan semoga usaha kita bersama senantiasa mendapat kesuksesan di hari ini maupun di masa mendatang.
                            <br>
                            Bersama surat ini kami informasikan bahwa sampai dengan saat ini kami belum menerima pembayaran atas tagihan service charge, sinking fund maupun utility untuk Unit <b>{{$unit_code}}</b> sesuai dengan tagihan yang sudah disampaikan sebelumnya kepada Bapak/Ibu. Adapun rincian atas tagihan-tagihan tersebut adalah sebagai berikut :</p>
                        @elseif($type_sp == 5)
                        <p style="line-height: 1.6;text-align: justify;text-justify: inter-word;">Dengan hormat, <br>
                            Perkenankan kami, Manajemen {{$company_name}}, mengucapkan terima kasih kepada Bapak/Ibu atas kerjasama yang telah terjalin dengan baik selama ini dan semoga usaha kita bersama senantiasa mendapat kesuksesan di hari ini maupun di masa mendatang.
                            <br>
                            Sehubungan dengan telah disampaikannya Surat Peringatan Pertama kepada Bapak/Ibu{{$last}}, maka dengan ini kami sampaikan kembali tagihan service charge, sinking fund maupun utility terhitung sampai dengan saat ini yaitu dengan rincian tagihan sebagai berikut :</p>
                        @else
                        <p style="line-height: 1.6;text-align: justify;text-justify: inter-word;">Dengan hormat, <br>
                            Perkenankan kami, Manajemen {{$company_name}}, mengucapkan terima kasih kepada Bapak/Ibu atas kerjasama yang telah terjalin dengan baik selama ini dan semoga usaha kita bersama senantiasa mendapat kesuksesan di hari ini maupun di masa mendatang.
                            <br>
                            Sehubungan dengan telah disampaikannya Surat Peringatan Kedua kepada Bapak/Ibu (terlampir), maka dengan ini kami sampaikan kembali sampai tagihan service charge, sinking fund maupun utility terhitung sampai dengan saat ini yaitu dengan rincian tagihan sebagai berikut :</p>
                        @endif
                    </td>
                </tr>
            </table>
            <b style="line-height: 1.6;">(Dalam Rupiah)</b>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    <td width="2%" style="border-collapse: collapse; border: solid 1px;"><b>No.</b></td>
                    <td width="20%" style="border-collapse: collapse; border: solid 1px;"><b>No.Invoice</b></td>
                    <td width="30%" style="border-collapse: collapse; border: solid 1px;"><b>Jenis.Invoice</b></td>
                    <td width="18%" style="border-collapse: collapse; border: solid 1px;"><b>Pokok</b></td>
                    <td width="18%" style="border-collapse: collapse; border: solid 1px;"><b>Denda</b></td>
                    <td width="18%" colspan="2" style="border-collapse: collapse; border: solid 1px;"><b>Hari Telat</b></td>
                </tr>
                <?php
                    if(!empty($inv['details'])){
                        $total = 0;
                        $no = 1;
                        $total_denda = 0;
                        foreach ($inv['details'] as $key => $value) {
                            $total += ($value['inv_outstanding'] == $value['inv_amount'] ? $value['inv_amount'] : $value['inv_outstanding']);
                            $total_denda += $value['denda_amount'];
                ?>
                <tr>
                    <td style="padding-top:5px"><center>{{$no}}</center></td>
                    <td style="border-left: solid 1px; text-align:center;">{{$value['inv_number']}}</td>
                    <td style="border-left: solid 1px; text-align:center;">{{$value['invtp_name']}}</td>
                    <td style="border-left: solid 1px; border-right: solid 1px; text-align: right;">{{number_format(($value['inv_outstanding'] == $value['inv_amount'] ? $value['inv_amount'] : $value['inv_outstanding']))}}
                    </td>
                    <td style="border-left: solid 1px; border-right: solid 1px; text-align: right;">{{number_format($value['denda_amount'])}}</td>
                    <td style="border-left: solid 1px; border-right: solid 1px; text-align: center;">{{$value['denda_days']}}</td>
                </tr>
               <?php
                    $no++;
                    }
                ?>
                <?php 
                if($manual !=NULL || $manual!=''){
                    $exp1 = explode("\n", $manual);
                    if(isset($exp1[0])){
                        for($k=0; $k<count($exp1); $k++){
                            $exp2 = explode("|", $exp1[$k]);
                            if(isset($exp2[0])){
                                echo '<tr>';
                                echo '<td style="padding-top:5px"><center>'.$no.'</center></td>';
                                echo '<td style="border-left: solid 1px; text-align:center;">'.$exp2[0].'</td>';
                                echo '<td style="border-left: solid 1px; text-align:center;">'.$exp2[1].'</td>';
                                echo '<td style="border-left: solid 1px; text-align:center;">'.$exp2[1].'</td>';
                                echo '<td style="border-left: solid 1px; text-align:center;">'.$exp2[1].'</td>';
                                echo '<td style="border-left: solid 1px; border-right: solid 1px; text-align: right;">'.number_format((float)$exp2[2]).'</td>';
                                echo '</td>';
                                $no++;
                                $total = $total + (float)$exp2[2];
                            }
                        }
                    }
                }
                ?>
                <tr style="border-top: 1px solid black;">
                    <td style="padding-top:5px">&nbsp;</td>
                    <td style="border-left: solid 1px;">&nbsp;</td>
                    <td style="border-left: solid 1px; text-align:right">&nbsp;</td>
                    <td style="border-left: solid 1px; text-align:right">{{number_format($total)}}</td>
                    <td style="border-left: solid 1px; text-align:right">{{number_format($total_denda)}}</td>
                    <td style="border-left: solid 1px; border-right: solid 1px; text-align: right;"><b>{{number_format($total+$total_denda)}}</b></td>
                </tr>
                <?php
                    }
                ?>
            </table>
            <br>
            <table width="100%">
                <tr>
                    <td style="line-height: 1.6;text-align: justify;text-justify: inter-word;">{!!$isicontent!!}</td>
                </tr>
            </table>
            <table width="100%" style="line-height: 18px;">
                <tr>
                    <td width="77%" style="vertical-align:top">&nbsp;</td>
                    <td width="23%" style="text-align: center; vertical-align: top;">
                        Hormat kami, <br><br>
                        @if(!empty($signature) && !empty($signatureFlag))
                        <?php if($type == 'pdf' || $type == 'mail'){ ?>
                        <img src="{{'file://'.base_path('public/'.$signature)}}" width="150">
                        <?php }else{ ?>
                        <img src="{{asset($signature)}}" width="150">
                        <?php } ?>
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