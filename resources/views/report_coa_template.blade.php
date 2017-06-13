
<table class="table table-striped" style="font-size: 10pt !important;"> 
    <thead>
      <tr>
        <th style="text-align: center;">Year</th>
        <th style="text-align: center;">Chart Account Code</th>
        <th style="text-align: center;">Chart Account Name</th>
        <th style="text-align: center;">COA Beginning</th>  
        <th style="text-align: center;">COA Debit</th>
        <th style="text-align: center;">COA Credit</th>
        <th style="text-align: center;">COA Ending</th>
      </tr>
    </thead>
        <tbody>
          @foreach($coa as $row)
        <tr>
          <td>{{$row['coa_year']}}</td>
          <td>{{trim($row['coa_code'])}}</td>
          <td>{{$row['coa_name']}}</td>
          <td>{{number_format($row['coa_beginning'],2)}}</td>
          <td>{{number_format($row['coa_debit'],2)}}</td>
          <td>{{number_format($row['coa_credit'],2)}}</td>
          <td>{{number_format($row['coa_ending'],2)}}</td>
        </tr>
        @endforeach
        
  </tbody>
</table>