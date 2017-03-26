<table class="table table-striped" width="100%"> 
    <thead>
      <tr>
        <th style="text-align: center;">No.Unit</th>
        <th style="text-align: center;">Jan</th>
        <th style="text-align: center;">Feb</th>
        <th style="text-align: center;">Mar</th>  
        <th style="text-align: center;">Apr</th>
        <th style="text-align: center;">May</th>
        <th style="text-align: center;">Jun</th>
        <th style="text-align: center;">Jul</th>
        <th style="text-align: center;">Aug</th>
        <th style="text-align: center;">Sep</th>
        <th style="text-align: center;">Okt</th>
        <th style="text-align: center;">Nov</th>
        <th style="text-align: center;">Des</th>
        <th style="text-align: center;">Total Consumption</th>
      </tr>
    </thead>
        <tbody>
          @foreach($invoices as $invoice)
        <tr style="text-align: center;">
          <td>{{$invoice['unit_code']}}</td>
          <td>{{number_format($invoice['jan'])}}</td>
          <td>{{number_format($invoice['feb'])}}</td>
          <td>{{number_format($invoice['mar'])}}</td>
          <td>{{number_format($invoice['apr'])}}</td>
          <td>{{number_format($invoice['may'])}}</td>
          <td>{{number_format($invoice['jun'])}}</td>
          <td>{{number_format($invoice['jul'])}}</td>
          <td>{{number_format($invoice['aug'])}}</td>
          <td>{{number_format($invoice['sep'])}}</td>
          <td>{{number_format($invoice['okt'])}}</td>
          <td>{{number_format($invoice['nov'])}}</td>
          <td>{{number_format($invoice['des'])}}</td>
          <td>{{number_format($invoice['total'])}}</td>
        </tr>
        @endforeach
        
  </tbody>
</table>