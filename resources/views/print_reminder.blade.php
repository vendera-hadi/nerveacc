<?php
    $company_name = $company['comp_name'];
    $company_title = $company['title'];
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
  <title>REMINDER</title>
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
            <div style="width: 100%"><img src="{{asset($company_logo)}}" style="width:110px;"/></div>

            <table style="width:100%;">
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td>Kepada:</td>
                            </tr>
                            <tr>
                                <td><strong>Yth. Bapak/Ibu Pemilik/Penghuni </strong></td>
                            </tr>
                            <tr>
                                <td><strong>Apartemen {{$company_title}}</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td>Jakarta, {{date('d F Y')}}</td>
                            </tr>
                            <tr>
                                <td>No. {{$id}}/FA/{{date('m')}}/{{str_slug($company_name, '-')}}/{{date('Y')}}</td>
                            </tr>
                                <td><b>REMINDER</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table style="margin-top: 70px">
                <tr>
                    <td>
                        <p>Dengan Hormat<br>Bersama ini kami mengingatkan kembali kerjasamanya, bahwa tagihan listrik/air, service charges/sinking fund dan asuransi Bapak/Ibu sampai dengan bulan <b>{{date('F Y', strtotime($invoice_data[0]->inv_date))}}</b> sudah jatuh tempo per tanggal <b>{{date('d F Y', strtotime($invoice_data[0]->inv_duedate))}}</b>. Bagi Bapak/Ibu yang belum melakukan pembayaran tagihan sampai bulan Oktober, kami berikan tenggang waktu sampai dengan tanggal <b>{{date('d F Y', strtotime($invoice_data[0]->inv_duedate))}}.</b> Apabila sampai dengan tanggal tersebut belum melunasi tagihan tersebut, maka dengan sangat terpaksa pihak Building Management akan melakukan penonaktifan kartu akses dan pemutusan aliran listrik/air ke unit Bapak/Ibu per tanggal <b>{{date('d F Y', strtotime('+1 day',strtotime($invoice_data[0]->inv_duedate)))}}</b><br><br>
                            Invoice yang harus dibayarkan:<br>
                            <ol>
                                @foreach($invoice_data as $val)
                                <li>{{$val->inv_number}} : IDR {{number_format($val->inv_outstanding,0)}}</li>
                                @endforeach
                            </ol>
                            <br><br>
                            Pembayaran dapat dilakukan melalui:<br>
                            <ol>
                                <li>Kasir di kantor Badan Pengelola</li>
                                <li>
                                    Transfer BCA Cabang Chase Plaza<br>
                                    No Virtual Account - PPPSRS Citilofts Sudirman<br>
                                    dengan <i>mencantumkan</i> <b>no faktur/no unit atau konfirmasi melalui email ke <a href="mailto:pengelolacls@gmail.com">pengelolacls@gmail.com</a> / <a href="mailto:pengelolacls.kasir@gmail.com">pengelolacls.kasir@gmail.com</a></b> atau <b>fax di 021-578 53620</b>
                                </li>
                            </ol><br><br>
                        Mohon konfirmasi apabila Bapak/Ibu sudah melakukan pembayaran sampai dengan tagihan bulan {{date('F Y', strtotime($invoice_data[0]->inv_date))}}, dan kami ucapkan terima kasih atas kerjasamanya
                        </p>
                    </td>
                </tr>
            </table>
            <hr>
            <table style="width:100%;">
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td>To:</td>
                            </tr>
                            <tr>
                                <td><strong>All Owners/Tenants</strong></td>
                            </tr>
                            <tr>
                                <td><strong>{{$company_title}} Apartment</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td><b>REMINDER</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table style="margin-top: 10px">
                <tr>
                    <td>
                        <p>With respect,<br>Herewith we would like to remind and your cooperation, your bill of electricity/water, service charges/sinking fund and insurance until <b>{{date('F Y', strtotime($invoice_data[0]->inv_date))}}</b> is due. For All Owners/Tenants who have not settled the payment until October, We give a time limit until <b>{{date('F d, Y', strtotime($invoice_data[0]->inv_duedate))}}</b>. But if until the time out you still have not settled the payment, the Building Management will non active access card and turn off the electricity/water on your unit apartment on <b>{{date('F d, Y', strtotime('+1 day',strtotime($invoice_data[0]->inv_duedate)))}}.</b><br><br>
                            Invoices that have to be paid:<br>
                            <ol>
                                @foreach($invoice_data as $val)
                                <li>{{$val->inv_number}} : IDR {{number_format($val->inv_outstanding,0)}}</li>
                                @endforeach
                            </ol>
                            <br><br>
                            Payments may be settled by:<br>
                            <ol>
                                <li>Cashier at Building Management Office</li>
                                <li>
                                    Transfer via BCA, Chase Plaza Branch<br>
                                    Virtual Account No- PPPSRS Citilofts Sudirman<br>
                                    by stating the <b>the invoice/unit number of your confirmation via email to <a href="mailto:pengelolacls@gmail.com">pengelolacls@gmail.com</a> / <a href="mailto:pengelolacls.kasir@gmail.com">pengelolacls.kasir@gmail.com</a></b> of <b>fax at 021-578 53620</b>
                                </li>
                            </ol><br><br>
                        Please confirm if you have already settled the bill until {{date('F Y', strtotime($invoice_data[0]->inv_date))}}, and we thank you for your cooperation.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="margin-top: 30px">
                        Sincerely,<br><br><br>
                        <b><u>{{$company_sign}}</u></b><br>
                        {{$company_position}}
                    </td>
                </tr>
            </table>
            <table width="100%">
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td>{{$company_address}}</td>
                            </tr>
                            <tr>
                                <td>{{$company_phone}}</td>
                            </tr>
                            <tr>
                                <td>email: pengelolacls@gmail.com</td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td>Managed By:</td>
                            </tr>
                            <tr>
                                <td>Jakarta Land Management</td>
                            </tr>
                            <tr><td></td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <script type="text/javascript">
         alert('The email has been sent to tenant');
    </script>
</body>
</html>