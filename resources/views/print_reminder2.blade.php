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
                                <td><strong>Bpk/Ibu {{$invoice_data[0]->MsTenant->tenan_name}}</strong></td>
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
                                <td><b>{{$title}}</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table style="margin-top: 70px">
                <tr>
                    <td>
                        {!!$content!!}

                        Invoice yang harus dibayarkan:<br>
                        <ol>
                            @foreach($invoice_data as $val)
                            <li>{{$val->inv_number}} : IDR {{number_format($val->inv_outstanding,0)}}</li>
                            @endforeach
                        </ol>
                        <br><br>
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