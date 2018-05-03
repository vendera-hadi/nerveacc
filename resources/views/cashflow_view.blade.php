<!DOCTYPE html>
<html>
<head>
    <title>Preview</title>
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/my_style.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-xs-12" >
            <center>
                <h4>
                    {{$company->comp_name}}<br>
                    <b>{{Request::get('title')}}</b><br>       
                    Tahun {{$tahun}}<br>
                    Dalam Rupiah (Rp)
                </h4>
            </center>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-condensed table-responsive tb">
                <tr>
                    <th>&nbsp;</th>
                    <th style="text-align: center;">JANUARI</th>
                    <th style="text-align: center;">FEBRUARI</th>
                    <th style="text-align: center;">MARET</th>
                    <th style="text-align: center;">APRIL</th>
                    <th style="text-align: center;">MAY</th>
                    <th style="text-align: center;">JUNI</th>
                    <th style="text-align: center;">JULI</th>
                    <th style="text-align: center;">AGUSTUS</th>
                    <th style="text-align: center;">SEPTEMBER</th>
                    <th style="text-align: center;">OKTOBER</th>
                    <th style="text-align: center;">NOVEMBER</th>
                    <th style="text-align: center;">DESEMBER</th>
                    <th style="text-align: center;">TOTAL {{$tahun}}</th>
                </tr>
                @foreach($detail as $dt)
                    @php
                        $desc = str_replace(' ','&nbsp;',$dt->desc);
                        $dt->settahun($tahun);
                        if(!empty($dt->header)) $desc = '<b>'.$desc.'</b>';
                        $dt->setVariables(0,$variables);
                        $dt->setVariables('jan',$v_jan);
                        $dt->setVariables('feb',$v_feb);
                        $dt->setVariables('mar',$v_mar);
                        $dt->setVariables('apr',$v_apr);
                        $dt->setVariables('may',$v_may);
                        $dt->setVariables('jun',$v_jun);
                        $dt->setVariables('jul',$v_jul);
                        $dt->setVariables('aug',$v_aug);
                        $dt->setVariables('sep',$v_sep);
                        $dt->setVariables('okt',$v_okt);
                        $dt->setVariables('nov',$v_nov);
                        $dt->setVariables('des',$v_des);
                        $jan = $dt->cashflowCalculate('1',$tahun);
                        $feb = $dt->cashflowCalculate('2',$tahun);
                        $mar = $dt->cashflowCalculate('3',$tahun);
                        $apr = $dt->cashflowCalculate('4',$tahun);
                        $may = $dt->cashflowCalculate('5',$tahun);
                        $jun = $dt->cashflowCalculate('6',$tahun);
                        $jul = $dt->cashflowCalculate('7',$tahun);
                        $aug = $dt->cashflowCalculate('8',$tahun);
                        $sep = $dt->cashflowCalculate('9',$tahun);
                        $okt = $dt->cashflowCalculate('10',$tahun);
                        $nov = $dt->cashflowCalculate('11',$tahun);
                        $des = $dt->cashflowCalculate('12',$tahun);
                        $total = $jan+$feb+$mar+$apr+$may+$jun+$jul+$aug+$sep+$okt+$nov+$des;           
                        if(!empty($dt->variable)) 
                        $v_jan[$dt->variable] = $jan;
                        $v_feb[$dt->variable] = $feb;
                        $v_mar[$dt->variable] = $mar;
                        $v_apr[$dt->variable] = $apr;
                        $v_may[$dt->variable] = $may;
                        $v_jun[$dt->variable] = $jun;
                        $v_jul[$dt->variable] = $jul;
                        $v_aug[$dt->variable] = $aug;
                        $v_sep[$dt->variable] = $sep;
                        $v_okt[$dt->variable] = $okt;
                        $v_nov[$dt->variable] = $nov;
                        $v_des[$dt->variable] = $des;

                    @endphp
                    @if($dt->hide == 0)
                        <tr>
                        <td>{!!$desc!!}</b></td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jan) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($feb) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($mar) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($apr) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($may) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jun) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jul) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($aug) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($sep) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($okt) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($nov) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($des) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($total) }}@endif</td>
                    @if($dt->linespace > 0)
                    @for($i=0; $i<count($dt->linespace); $i++)
                    <tr>
                        <td colspan="2" style="height:20px"></td>
                    </tr>
                    @endfor
                    @endif
                @endif
                @endforeach
            </table>
        </div>
    </div>
</body>
</html>

<?php if($jenis == 'print'){ ?>
    <script type="text/javascript">
        window.print();
    </script>
<?php } ?>