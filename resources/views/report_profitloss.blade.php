<center>
    <h4 style="font-size: 16px;">
        LAPORAN LABA RUGI<br>
        {{$company->comp_name}}<br>
        {{$datetxt}}
    </h4>

    <table width="600px" style="margin-top:60px">
        @foreach($detail as $dt)
        @php
            $desc = str_replace(' ','&nbsp;',$dt->desc);
            $dt->setDate($from, $to);
            if(!empty($dt->header)) $desc = '<b style="font-size:16px">'.$desc.'</b>';
            $dt->setVariables($variables);
            $calculate = $dt->calculateAccount();
            if(!empty($dt->variable)) $variables[$dt->variable] = $calculate;
        @endphp
        @if($dt->hide == 0)
            <tr>
                <td>{!!$desc!!}</b></td>
                <td style="text-align:right; @if($dt->underline != 0) border-bottom: 1px solid black @endif">@if(!empty($dt->coa_code) || !empty($dt->formula)){{ 'Rp. '.number_format($calculate, 0, ',', '.') }}@endif</td>
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
</center>