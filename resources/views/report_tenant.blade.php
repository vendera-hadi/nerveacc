<table class="table table-striped" style="font-size: 10pt !important;"> 
    <thead>
      <tr>
        <th style="text-align: center;">Nama Tenan</th>
        <th style="text-align: center;">No NIP</th>
        <th style="text-align: center;">Phone</th>
        <th style="text-align: center;">Fax</th>  
        <th style="text-align: center;">Email</th>
        <th style="text-align: center;">Address</th>
        <th style="text-align: center;">NPWP</th>
        <th style="text-align: center;">Tax Name</th>
        <th style="text-align: center;">Tax Address</th>
      </tr>
    </thead>
        <tbody>
          @foreach($invoices as $invoice)
        <tr>
          <td>{{$invoice['tenan_name']}}</td>
          <td>{{$invoice['tenan_idno']}}</td>
          <td>{{$invoice['tenan_phone']}}</td>
          <td>{{$invoice['tenan_fax']}}</td>
          <td>{{$invoice['tenan_email']}}</td>
          <td>{{$invoice['tenan_address']}}</td>
          <td>{{$invoice['tenan_npwp']}}</td>
          <td>{{$invoice['tenan_taxname']}}</td>
          <td>{{$invoice['tenan_tax_address']}}</td>
        </tr>
        @endforeach
        
  </tbody>
</table>