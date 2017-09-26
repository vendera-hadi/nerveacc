<?php
    $company_name = $company['comp_name'];
    $company_logo = 'upload/'.$company['comp_image'];
    $company_address = $company['comp_address'];
    $company_phone = $company['comp_phone'];
    $company_fax = $company['comp_fax'];
    $company_sign = $company['comp_sign_inv_name'];
    $company_position = @$company['comp_sign_position'];
?>
<!DOCTYPE html>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <head>
    <title>Cetak Report</title>
    <style>
    <?php include(public_path().'/css/bootstrap.css');?>
    .poinfo tr td{
      border:none !important;
    }
    </style>
    <link href="{{ asset('/css/AdminLTE.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('/css/skins/skin-blue.css') }}" rel="stylesheet" type="text/css" />

    
  </head>
    <body>
    <!-- Main content -->
    <section class="invoice">
    <div class="box-body table-responsive">
      <table class="" width="100%" style="margin-bottom: 10px;">
        <tr class="page-header">
          <td width="50%">
            <h3>
              Purchase Order #{{$po->po_number}}
            </h3>
          </td>
          <td width="50%">
            <img src="@if(!empty($logo)){{asset('upload/'.$logo)}}@endif" width="150" class="pull-right">
          </td>
        </tr>
        <tr>
          <td width="60%" style="padding-top: 35px; vertical-align: top;">
            <table width="100%">
              <tbody>
                <tr>
                    <td>Supplier:</td>
                </tr>
                <tr>
                    <td><b>{{$po->supplier->spl_name}}</b></td>
                </tr>
                <tr>
                    <td>{{$po->supplier->spl_address}}</td>
                </tr>
                <tr>
                    <td>{{$po->supplier->spl_city.", ".$po->supplier->spl_postal_code}}</td>
                </tr>
                <tr>
                    <td>Phone : {{$po->supplier->spl_phone ?: "-"}}</td>
                </tr>
                <tr>
                    <td>Fax : {{$po->supplier->spl_fax ?: "-"}}</td>
                </tr>
              </tbody>
            </table>
          </td>
          <td width="40%" style="padding-top: 35px;">
            <table width="100%" class="table">
              <tbody class="poinfo">
              <tr>
                <td>PO Date</td><td>:</td><td>{{date('d/m/Y',strtotime($po->po_date))}}</td>
              </tr>
              <tr>
                <td>PO Due Date</td><td>:</td><td>{{date('d/m/Y',strtotime($po->due_date))}}</td>
              </tr>
              <tr>
                <td>Note</td><td>:</td><td>{{$po->note ?: "-"}}</td>
              </tr>
              <tr>
                <td>Payment Terms</td><td>:</td><td>{{$po->terms}}</td>
              </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </table>
      <br>
            <table width="100%" style="border-collapse: collapse; border: solid 1px; line-height: 18px;">
                <tr style="text-align: center;">
                    
                    <td width="50%" style="border-collapse: collapse; border: solid 1px;"><b>KETERANGAN</b><br><i>Description</i></td>
                    <td width="20%"  style="border-collapse: collapse; border: solid 1px;"><b>HARGA SATUAN</b><br><i>Price Each</i></td>
                    <td width="10%"  style="border-collapse: collapse; border: solid 1px;"><b>JUMLAH</b><br><i>Qty</i></td>
                    <td width="20%"  style="border-collapse: collapse; border: solid 1px;"><b>SUBTOTAL</b><br><i>Subtotal</i></td>
                </tr>
                @php $total = $totalppn = 0; @endphp
                @foreach($po->detail as $value)
                @php
                  $total += $value->amount * $value->qty;
                  $totalppn += $value->ppn_amount;
                @endphp
                <tr style="text-align: center;">
                    <td style="vertical-align: top; padding-left:15px; padding-right:10px; padding-top:10px; border-right: solid 1px">
                        {{$value->note}}
                    </td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px; border-right: solid 1px">
                        <div style="padding-right: 3px;">Rp. <?php echo number_format($value->amount,2);?></div>
                    </td>
                    <td style="vertical-align: top; padding-left:15px; padding-right:10px; padding-top:10px; border-right: solid 1px">
                        {{$value->qty}}
                    </td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px">
                        <div style="padding-right: 3px;">Rp. {{number_format($value->amount * $value->qty,2)}}</div>
                    </td>
                </tr>
                @endforeach
                <tr style="border-top: 1px solid black;">
                    
                    <td colspan="3" style="padding-left:15px; padding-right:10px; padding-bottom:10px; padding-top:10px; border-right: solid 1px"><b>SUBTOTAL</b></td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px"><b>Rp. {{number_format($total,0)}}</b></td>
                </tr>

                <tr style="border-top: 1px solid black;">
                    
                    <td colspan="3" style="padding-left:15px; padding-right:10px; padding-bottom:10px; padding-top:10px; border-right: solid 1px"><b>TAX</b></td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px"><b>Rp. {{number_format($totalppn,0)}}</b></td>
                </tr>

                <tr style="border-top: 1px solid black;">
                    
                    <td colspan="3" style="padding-left:15px; padding-right:10px; padding-bottom:10px; padding-top:10px; border-right: solid 1px"><b>TOTAL</b></td>
                    <td style="border-collapse: collapse; text-align: right; padding-right:15px"><b>Rp. {{number_format($total + $totalppn,0)}}</b></td>
                </tr>

            </table>
      <div style="font-size: 9pt !important;">
      
      </div>

        <table width="100%" style="line-height: 18px; margin-top:100px">
            <tr>
                <td width="77%" style="vertical-align:top">
                    {!!$footer!!}<br><br>
                    {!!$label!!}
                </td>
                <td width="23%" style="text-align: center; vertical-align: top;">
                    Jakarta, <?php echo date('d M Y'); ?>
                    <br><br><br><br><br>
                    <b><u>{!!$signature!!}</u></b><br>
                    {!!$position!!}
                    
                </td>
            </tr>
            
        </table>
    </div>
      </section>

      <script type="text/javascript">
        window.print();
    </script>
  </body>
  </html>