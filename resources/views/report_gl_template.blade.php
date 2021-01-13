<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th>Date</th>
        <th>No Jurnal</th>
        <th>COA</th>
        <th>COA Name</th>
        <th>Invoice No</th>
        <th>Description</th>
        <th>Debit</th>
        <th>Credit</th>
        <th>Type</th> 
      </tr>
    </thead>
        <tbody>
            @foreach($ledger as $ledg)
            <tr>
                <td>{{date('d/m/Y',strtotime($ledg->ledg_date))}}</td>
                <td>{{$ledg->ledg_number}}</td>
                <td>{{$ledg->coa_code}}</td>
                <td>{{$ledg->coa_name}}</td>
                <td>{{!empty($ledg->ledg_refno) ? $ledg->ledg_refno : '-'}}</td>
                <td>{{$ledg->ledg_description}}</td>
                <td>{{"Rp. ".number_format($ledg->ledg_debit,2)}}</td>
                <td>{{"Rp. ".number_format($ledg->ledg_credit,2)}}</td>
                <td>{{$ledg->jour_type_prefix}}</td>
            </tr>
            @endforeach
        </tbody>
</table>