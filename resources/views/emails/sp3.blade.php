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
                                <td>No. {{@$invoice->id}}/SP3/{{date('m')}}/{{str_slug(@$company->comp_name, '-')}}/{{date('Y')}}</td>
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

<?php if(isset($print)){ ?>
    <script type="text/javascript">
         window.print();
    </script>
<?php } ?>

</body>
</html>