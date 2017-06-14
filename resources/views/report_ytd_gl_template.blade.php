<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
      	<th>Date</th>
        <th>Acc No</th>
        <th>Description</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Jrnl Type</th>
        <th>Balance</th> 
      </tr>
    </thead>
        <tbody>
            @foreach($ledger as $key => $ledg_per_month)
            @php
            $totalDebit = 0;
            $totalCredit = 0;
            @endphp
            @foreach($ledg_per_month as $ledg)
            @php
            $totalDebit += $ledg->ledg_debit;
            $totalCredit += $ledg->ledg_credit;
            @endphp
            <tr>
            	<td>{{date('d/m/Y',strtotime($ledg->ledg_date))}}</td>
                <td>{{$ledg->coa_code}}</td>
                <td>{{$ledg->ledg_description}}</td>
                <td>{{"Rp. ".number_format($ledg->ledg_debit,2)}}</td>
                <td>{{"Rp. ".number_format($ledg->ledg_credit,2)}}</td>
                <td>{{$ledg->jour_type_prefix}}</td>
                <td></td>
            </tr>
            @endforeach
            <tr>
                <td colspan="3"><strong><< {{date('F Y',strtotime(date($key."-01") ))}} >></strong></td>
                <td style="border-top:2px dashed black !important">Rp. {{number_format($totalDebit,2)}}</td>
                <td style="border-top:2px dashed black !important">Rp. {{number_format($totalCredit,2)}}</td>
                <td></td>
                <td>
                    @if($balances[$key] < 0) 
                    (Rp. {{number_format($balances[$key],2)}})
                    @else
                    Rp. {{number_format($balances[$key],2)}}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
</table>