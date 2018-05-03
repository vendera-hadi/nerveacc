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
        <div class="col-lg-12 col-md-12 col-xs-12" >
            <table class="table table-bordered table-condensed table-responsive tb">
                <tr>
                    <th>&nbsp;</th>
                    <th style="text-align: center;" colspan="2">JANUARI</th>
                    <th style="text-align: center;" colspan="2">FEBRUARI</th>
                    <th style="text-align: center;" colspan="2">MARET</th>
                    <th style="text-align: center;" colspan="2">APRIL</th>
                    <th style="text-align: center;" colspan="2">MAY</th>
                    <th style="text-align: center;" colspan="2">JUNI</th>
                    <th style="text-align: center;" colspan="2">JULI</th>
                    <th style="text-align: center;" colspan="2">AGUSTUS</th>
                    <th style="text-align: center;" colspan="2">SEPTEMBER</th>
                    <th style="text-align: center;" colspan="2">OKTOBER</th>
                    <th style="text-align: center;" colspan="2">NOVEMBER</th>
                    <th style="text-align: center;" colspan="2">DESEMBER</th>
                    <th style="text-align: center;" colspan="2">TOTAL {{$tahun}}</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Budget</th>
                    <th style="text-align: center;">Realisasi</th>
                </tr>
                @foreach($detail as $dt)
                    @php
                        $desc = str_replace(' ','&nbsp;',$dt->desc);
                        $dt->settahun($tahun);
                        if(!empty($dt->header)) $desc = '<b>'.$desc.'</b>';
                        if(!empty($dt->variable)) 
                        $dt->setVariables('jan',$v_jan,1);
                        $dt->setVariables('feb',$v_feb,1);
                        $dt->setVariables('mar',$v_mar,1);
                        $dt->setVariables('apr',$v_apr,1);
                        $dt->setVariables('may',$v_may,1);
                        $dt->setVariables('jun',$v_jun,1);
                        $dt->setVariables('jul',$v_jul,1);
                        $dt->setVariables('aug',$v_aug,1);
                        $dt->setVariables('sep',$v_sep,1);
                        $dt->setVariables('okt',$v_okt,1);
                        $dt->setVariables('nov',$v_nov,1);
                        $dt->setVariables('des',$v_des,1);
                        $dt->setVariables('jan',$j_jan,2);
                        $dt->setVariables('feb',$j_feb,2);
                        $dt->setVariables('mar',$j_mar,2);
                        $dt->setVariables('apr',$j_apr,2);
                        $dt->setVariables('may',$j_may,2);
                        $dt->setVariables('jun',$j_jun,2);
                        $dt->setVariables('jul',$j_jul,2);
                        $dt->setVariables('aug',$j_aug,2);
                        $dt->setVariables('sep',$j_sep,2);
                        $dt->setVariables('okt',$j_okt,2);
                        $dt->setVariables('nov',$j_nov,2);
                        $dt->setVariables('des',$j_des,2);

                        $jan = $dt->cashflowCalculate('1',$tahun,1);
                        $feb = $dt->cashflowCalculate('2',$tahun,1);
                        $mar = $dt->cashflowCalculate('3',$tahun,1);
                        $apr = $dt->cashflowCalculate('4',$tahun,1);
                        $may = $dt->cashflowCalculate('5',$tahun,1);
                        $jun = $dt->cashflowCalculate('6',$tahun,1);
                        $jul = $dt->cashflowCalculate('7',$tahun,1);
                        $aug = $dt->cashflowCalculate('8',$tahun,1);
                        $sep = $dt->cashflowCalculate('9',$tahun,1);
                        $okt = $dt->cashflowCalculate('10',$tahun,1);
                        $nov = $dt->cashflowCalculate('11',$tahun,1);
                        $des = $dt->cashflowCalculate('12',$tahun,1);
                        $total = $jan+$feb+$mar+$apr+$may+$jun+$jul+$aug+$sep+$okt+$nov+$des;

                        
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

                        $jan_budget = $dt->cashflowCalculate('1',$tahun,2);
                        $feb_budget = $dt->cashflowCalculate('2',$tahun,2);
                        $mar_budget = $dt->cashflowCalculate('3',$tahun,2);
                        $apr_budget = $dt->cashflowCalculate('4',$tahun,2);
                        $may_budget = $dt->cashflowCalculate('5',$tahun,2);
                        $jun_budget = $dt->cashflowCalculate('6',$tahun,2);
                        $jul_budget = $dt->cashflowCalculate('7',$tahun,2);
                        $aug_budget = $dt->cashflowCalculate('8',$tahun,2);
                        $sep_budget = $dt->cashflowCalculate('9',$tahun,2);
                        $okt_budget = $dt->cashflowCalculate('10',$tahun,2);
                        $nov_budget = $dt->cashflowCalculate('11',$tahun,2);
                        $des_budget = $dt->cashflowCalculate('12',$tahun,2);
                        $total_budget = $jan_budget+$feb_budget+$mar_budget+$apr_budget+$may_budget+$jun_budget+$jul_budget+$aug_budget+$sep_budget+$okt_budget+$nov_budget+$des_budget;

                        $j_jan[$dt->variable] = $jan_budget;
                        $j_feb[$dt->variable] = $feb_budget;
                        $j_mar[$dt->variable] = $mar_budget;
                        $j_apr[$dt->variable] = $apr_budget;
                        $j_may[$dt->variable] = $may_budget;
                        $j_jun[$dt->variable] = $jun_budget;
                        $j_jul[$dt->variable] = $jul_budget;
                        $j_aug[$dt->variable] = $aug_budget;
                        $j_sep[$dt->variable] = $sep_budget;
                        $j_okt[$dt->variable] = $okt_budget;
                        $j_nov[$dt->variable] = $nov_budget;
                        $j_des[$dt->variable] = $des_budget;
                    @endphp
                    @if($dt->hide == 0)
                        <tr>
                        <td>{!!$desc!!}</b></td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jan_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jan) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($feb_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($feb) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($mar_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($mar) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($apr_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($apr) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($may_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($may) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jun_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jun) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jul_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($jul) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($aug_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($aug) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($sep_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($sep) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($okt_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($okt) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($nov_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($nov) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($des_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($des) }}@endif</td>

                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($total_budget) }}@endif</td>
                        <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($total) }}@endif</td>

                    @if($dt->linespace > 0)
                    @for($i=0; $i<count($dt->linespace); $i++)
                    <tr>
                        <td colspan="4" style="height:20px"></td>
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