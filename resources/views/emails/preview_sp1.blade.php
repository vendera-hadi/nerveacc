<!DOCTYPE html>
<html>
<head>
  <title>SURAT SP1</title>
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
            <div style="width: 100%"><img src="{{asset('upload/'.@$company->comp_image)}}" style="width:110px;"/></div>

            <table style="width:100%;">
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td>Kepada: {{$company->title}}</td>
                            </tr>
                            <tr>
                                <td><strong>Yth. Bapak/Ibu Pemilik/Penghuni </strong></td>
                            </tr>
                            <tr>
                                <td><strong>Nama Tenant (Unit)</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Apartemen {{@$company->title}}</strong></td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%">
                        <table width="100%">
                            <tr>
                                <td>Jakarta, {{date('d F Y')}}</td>
                            </tr>
                            <tr>
                                <td>No. XXX/SP1/{{date('m')}}/{{str_slug(@$company->comp_name, '-')}}/{{date('Y')}}</td>
                            </tr>
                                <td><b>{{$emailtpl->title}}</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table style="margin-top: 70px">
                <tr>
                    <td style="text-align: justify;">
                       <p>Dengan Hormat,<br><br>Bersama ini kami mengingatkan kembali kerjasamanya, bahwa tagihan utilitas dan tagihan iuran pengelolaan lingkungan Bapak/Ibu sampai dengan bulan <b>Januari 2018</b> sudah jatuh tempo per tanggal <b>15 Februari 2018</b>. Bagi Bapak/Ibu yang belum melakukan pembayaran tagihan sampai bulan Februari, kami berikan tenggang waktu sampai dengan tanggal <b>22 Februari 2018.</b> Guna menghindari denda
keterlambatan yang semakin besar, maka kami sarankan kepada Bapak/Ibu untuk dapat segera
menyelesaikan kewajiban atas tagihan yang telah jatuh tempo maupun denda keterlambatan yang
telah diperhitungkan sebelum tanggal <b>22 Februari 2018</b>
                        <br><br>
                        <table width="100%">
                            <tr>
                                <td width="50%">
                                    Invoice Utilities yang harus dibayarkan:<br>
                                    <ol>
                                        <li>UT -1801-XXXX : IDR XXXXX</li>
                                        <li>UT -1801-XXXX : IDR XXXXX</li>
                                    </ol>
                                    <br><br>
                                    <b>No. Virtual Account Invoice Utilities: 123456789</b>
                                </td>
                                <td width="50%">
                                    Invoice Maintenance yang harus dibayarkan:<br>
                                    <ol>
                                        <li>MN -1801-XXXX : IDR XXXXX</li>
                                        <li>MN -1801-XXXX : IDR XXXXX</li>
                                    </ol>
                                    <br><br>
                                    <b>No. Virtual Account Invoice Maintenance: 1234567890</b>
                                </td>
                            </tr>

                        </table><br>
                        </p>
                        <!-- custom message -->
                        {!!$emailtpl->content!!}
                        <br><br>
                    </td>
                </tr>
            </table>

            <table width="100%">
                <tr>
                    <td width="60%">
                        <table width="100%">
                            <tr>
                                <td>Hormat kami,</td>
                            </tr>
                            <tr>
                                <td>Pengelola <b>{{@$company->comp_name}}</b></td>
                            </tr>
                            <tr>
                                <td>{{@$company->comp_address}}</td>
                            </tr>
                            <tr>
                                <td>{{@$company->comp_phone}}</td>
                            </tr>
                            <tr>
                                <td>email: {{@$company->email}}</td>
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

</body>
</html>