<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th>Acc No</th>
        <th>Acc Name</th>
        <th>Date</th>
        <th>Invoice No</th>
        <th>Description</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Jrnl Type</th>
        <th>Tenant Name</th>    
      </tr>
    </thead>
        <tbody>
            @foreach($ledger as $ledg)
            <tr>
                <td>{{$ledg->coa_code}}</td>
                <td>{{$ledg->coa_name}}</td>
                <td>{{date('d/m/Y',strtotime($ledg->ledg_date))}}</td>
                <td>{{!empty($ledg->ledg_refno) ? $ledg->ledg_refno : '-'}}</td>
                <td>{{$ledg->ledg_description}}</td>
                <td>{{"Rp. ".$ledg->ledg_debit}}</td>
                <td>{{"Rp. ".$ledg->ledg_credit}}</td>
                <td>{{$ledg->jour_type_prefix}}</td>
                <td>{{!empty($ledg->tenan_name) ? $ledg->tenan_name : '-'}}</td>
            </tr>
            @endforeach
        </tbody>
</table>