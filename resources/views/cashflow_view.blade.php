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
                    <th style="min-width: 300px;">&nbsp;</th>
                    <th style="text-align: center;min-width: 80px;">Jan-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Feb-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Mar-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Apr-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">May-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Jun-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Jul-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Aug-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Sep-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Oct-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Nov-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">Dec-{{$tahun}}</th>
                    <th style="text-align: center;min-width: 80px;">YTD</th>   
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
                        $jan = $dt->cashflowledgerCalculate('1',$tahun);
                        $feb = $dt->cashflowledgerCalculate('2',$tahun);
                        $mar = $dt->cashflowledgerCalculate('3',$tahun);
                        $apr = $dt->cashflowledgerCalculate('4',$tahun);
                        $may = $dt->cashflowledgerCalculate('5',$tahun);
                        $jun = $dt->cashflowledgerCalculate('6',$tahun);
                        $jul = $dt->cashflowledgerCalculate('7',$tahun);
                        $aug = $dt->cashflowledgerCalculate('8',$tahun);
                        $sep = $dt->cashflowledgerCalculate('9',$tahun);
                        $okt = $dt->cashflowledgerCalculate('10',$tahun);
                        $nov = $dt->cashflowledgerCalculate('11',$tahun);
                        $des = $dt->cashflowledgerCalculate('12',$tahun);
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