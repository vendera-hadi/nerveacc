<!DOCTYPE html>
<html>
<head>
  <title>SURAT SP2</title>
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
                                <td><strong>{{$invoice->MsTenant->tenan_name}} @if(!empty(@$invoice->unit)) (Unit {{$invoice->unit->unit_name}}) @endif</strong></td>
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
                                <td>No. {{@$invoice->id}}/SP2/{{date('m')}}/{{str_slug(@$company->comp_name, '-')}}/{{date('Y')}}</td>
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
                       <p>Dengan Hormat,<br><br>Bersama ini kami mengingatkan kembali kerjasamanya, bahwa tagihan utilitas dan tagihan iuran pengelolaan lingkungan Bapak/Ibu sampai dengan bulan <b>{{convertbulan(date('m', strtotime($invoice->inv_date)))}} {{date('Y', strtotime($invoice->inv_date))}}</b> sudah jatuh tempo per tanggal <b>{{date('d', strtotime($invoice->inv_duedate))}} {{convertbulan(date('m', strtotime($invoice->inv_date)))}} {{date('Y', strtotime($invoice->inv_duedate))}}</b>. Bagi Bapak/Ibu yang belum melakukan pembayaran tagihan sampai bulan {{convertbulan(date('m', strtotime($invoice->inv_duedate)))}}, kami berikan tenggang waktu sampai dengan tanggal <b>{{date('d', strtotime('+14 day',strtotime($invoice->inv_duedate)))}} {{convertbulan(date('m', strtotime('+14 day',strtotime($invoice->inv_duedate))))}} {{date('Y', strtotime('+14 day', strtotime($invoice->inv_duedate)))}}.</b> Guna menghindari denda
keterlambatan yang semakin besar, maka kami sarankan kepada Bapak/Ibu untuk dapat segera
menyelesaikan kewajiban atas tagihan yang telah jatuh tempo maupun denda keterlambatan yang
telah diperhitungkan sebelum tanggal <b>{{date('d', strtotime('+14 day',strtotime($invoice->inv_duedate)))}} {{convertbulan(date('m', strtotime('+14 day',strtotime($invoice->inv_duedate))))}} {{date('Y', strtotime('+14 day', strtotime($invoice->inv_duedate)))}}</b>
                        <br><br>
                        <table width="100%">
                            <tr>
                                @if(count($utilities_invoices) > 0)
                                <td width="50%">
                                    Invoice Utilities yang harus dibayarkan:<br>
                                    <ol>
                                        @foreach($utilities_invoices as $val)
                                        <li>{{$val->inv_number}} : IDR {{number_format($val->inv_outstanding,0)}}</li>
                                        @endforeach
                                    </ol>
                                    <br><br>
                                    <b>No. Virtual Account Invoice Utilities: {{@$val->unit->va_utilities}}</b>
                                </td>
                                @endif
                                @if(count($maintenance_invoices) > 0)
                                <td width="50%">
                                    Invoice Maintenance yang harus dibayarkan:<br>
                                    <ol>
                                        @foreach($maintenance_invoices as $val)
                                        <li>{{$val->inv_number}} : IDR {{number_format($val->inv_outstanding,0)}}</li>
                                        @endforeach
                                    </ol>
                                    <br><br>
                                    <b>No. Virtual Account Invoice Maintenance: {{@$val->unit->va_maintenance}}</b>
                                </td>
                                @endif
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
                                <td style="padding-top: 90px;font-size: 10px;">Managed By: PT. Jakarta Land Management</td>
                            </tr>
                            <tr>
                                <td></td>
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