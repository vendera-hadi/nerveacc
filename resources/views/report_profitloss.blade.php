<!DOCTYPE html>
<html>
<head>
    <title>Preview</title>
    <link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/AdminLTE.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/skins/_all-skins.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/my_style.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <center>
                    <h4>
                        {{$company->comp_name}}<br>
                        <b>{{Request::get('title')}}</b><br>       
                        {{$datetxt}}<br>
                        Dalam Rupiah (Rp)
                    </h4>
                </center>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-stripped table-condensed">
                    @foreach($detail as $dt)
                    @php
                        $desc = str_replace(' ','&nbsp;',$dt->desc);
                        $dt->setDate($from, $to);
                        if(!empty($dt->header)) $desc = '<b>'.$desc.'</b>';
                        $dt->setVariables($variables);
                        $calculate = $dt->calculateAccount();
                        if(!empty($dt->variable)) $variables[$dt->variable] = $calculate;
                    @endphp
                    @if($dt->hide == 0)
                        <tr>
                            <td>{!!$desc!!}</b></td>
                            <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ format_report_numeric($calculate) }}@endif</td>
                        </tr>
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
    </div>
</body>
</html>