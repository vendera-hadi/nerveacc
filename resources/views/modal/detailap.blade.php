<div class="row">
  <div class="col-sm-12">
    <table>
      <tr>
        <td width="120"><strong>Supplier</strong></td><td width="15">:</td><td>{{$ap->supplier->spl_name}} ({{$ap->supplier->spl_code}})</td>
      </tr>
      <tr>
        <td width="120"><strong>Invoice Date</strong></td><td width="15">:</td><td>{{date('d F Y', strtotime($ap->invoice_date))}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Invoice Due Date</strong></td><td width="15">:</td><td>{{date('d F Y', strtotime($ap->invoice_duedate))}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Invoice Number</strong></td><td width="15">:</td><td>{{$ap->invoice_no}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Total</strong></td><td width="15">:</td><td>{{"IDR ".number_format($ap->total,0,',','.')}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Discount</strong></td><td width="15">:</td><td>{{"IDR ".number_format($ap->discount,0,',','.')}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Grand total</strong></td><td width="15">:</td><td>{{"IDR ".number_format($ap->final_total,0,',','.')}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Outstanding</strong></td><td width="15">:</td><td>{{"IDR ".number_format($ap->outstanding,0,',','.')}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Posted</strong></td><td width="15">:</td><td>{{!empty($ap->posting) ? "yes" : "no"}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Note</strong></td><td width="15">:</td><td>{{$ap->note}}</td>
      </tr>
      <tr>
        <td width="120"><strong>PO</strong></td><td width="15">:</td><td>{{@$ap->po ? $ap->po->po_number : "no"}}</td>
      </tr>
      <tr>
        <td width="120"><strong>Terms</strong></td><td width="15">:</td><td>{{$ap->terms}}</td>
      </tr>
      </table>
  </div>

  <div class="col-sm-12">
      <h3>Details</h3>
      <table  width="100%" class="table table-bordered" style="font-size:12px !important;">
        <tr>
          <th class="text-center">COA Type</th>
          <th class="text-center">COA Code</th>
          <th class="text-center">Desc</th>
          <th class="text-center">Qty</th>
          <th class="text-center">Amount</th>
          <th class="text-center">Dept Name</th>
        </tr>
        @foreach($ap->detail as $detail)
        <tr>
          <td>{{$detail->coa_type}}</td>
          <td>{{$detail->coa_code." ".$detail->coa->coa_name}}</td>
          <td>{{$detail->note}}</td>
          <td>{{$detail->qty}}</td>
          <td>{{"IDR ".number_format($detail->amount,0,',','.')}}</td>
          <td>{{$detail->dept->dept_name}}</td>
        </tr>
        @endforeach
      </table>
  </div>
</div>