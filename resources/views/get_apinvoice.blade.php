<p><b>Pilih Invoice yang akan dibayar</b></p>
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th width="10" ></th>
                <th width="100">No.Invoice</th>  
                <th width="100">No. PO</th>
                <th width="50">terms</th>
                <th width="50">Tgl Invoice</th>
                <th width="50">Jatuh Tempo</th>
                <th width="80">Outstanding Amount</th>
                <th width="100">Terbayar</th>  
            </tr>
        </thead>
        <tbody>
            @if(!empty($invoice_data))
                @foreach ($invoice_data as $key => $value)
                    <?php
                        $inv_duedate = strtotime($value['invoice_duedate']);
                        $inv_date = strtotime($value['invoice_date']);
                        $inv_id = $value['id'];

                        $now_time = strtotime(date('Y-m-d'));

                        $class = '';
                        if($inv_duedate < $now_time ){
                            $class = 'danger';
                        }
                    ?>
                <tr class="{{ $class }}">
                    <td><input type="checkbox" name="amount[{{ $inv_id }}]" value="{{ $value['outstanding'] }}" class="paid-check"></td>
                    <td>{{ $value['invoice_no'] }}</td>
                    <td>{{ $value['po_number'] }}</td>
                    <td>{{ $value['terms'] }}</td>
                    <td>{{ date('d/m/y', $inv_date) }}</td>
                    <td>{{ date('d/m/y', $inv_duedate) }}</td>
                    <td>{{ 'Rp. '.number_format($value['outstanding']) }}</td>
                    <td><input type="text" name="pay[{{$inv_id}}]" value="{{floor($value['outstanding'])}}" maxlength="{{floor($value['outstanding'])}}" minlength="1" placeholder="Jumlah Bayar / Total Paid" class="form-control paid-amount" disabled=""></td>
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="7">Data not found</td>
            </tr>
            @endif

            <tr>
                <td colspan="7"><span class="pull-right">Grand Total</span></td>
                <td>Rp. <span id="totalCash">0</span></td>
            </tr> 
        </tbody>
    </table>
</div>

@if(!empty($invoice_data))
<script type="text/javascript">
function reCount(){
    var total = 0;
    $('.paid-amount').each(function(){
        if($(this).parents('tr').find('.paid-check').is(':checked')){
            $(this).removeAttr('disabled');
            console.log(parseFloat($(this).val()));
            total = total + parseFloat($(this).val());
        }
    });
    $('#totalCash').text(addCommas(total)); 
}
function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
$('.paid-check,.paid-amount').change(function(){
    reCount();
});
</script>
@endif