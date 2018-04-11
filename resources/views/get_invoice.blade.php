<p><b>Pilih Pembayaran Invoice yang mana</b></p>
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th width="10" ></th>
                <th width="100">No.Invoice</th>
                <th width="100">Unit</th>
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
                        $inv_duedate = strtotime($value->inv_duedate);
                        $inv_date = strtotime($value->inv_date);
                        $inv_id = $value->id;

                        $now_time = strtotime(date('Y-m-d'));

                        $class = '';
                        if($inv_duedate < $now_time ){
                            $class = 'danger';
                        }
                    ?>
                <tr class="<?php echo $class;?>">
                    <td><input type="checkbox" name="data_payment[invpayd_amount][<?php echo $inv_id;?>]" value="<?php echo $value->inv_outstanding;?>" class="paid-check"></td>
                    <td><?php echo $value->inv_number;?></td>
                    <td>{{ !empty(@$value->unit) ? $value->unit->unit_code : @$value->unit_name }}</td>
                    <td><?php echo date('d/m/y', $inv_date);?></td>
                    <td><?php echo date('d/m/y', $inv_duedate);?></td>
                    <td><?php echo 'Rp. '.number_format($value->inv_outstanding);?></td>
                    <td><input type="number" name="data_payment[totalpay][{{$inv_id}}]" value="{{floor($value->inv_outstanding)}}"  min="1" placeholder="Jumlah Bayar / Total Paid" class="form-control paid-amount" disabled=""></td>
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="7">Data not found</td>
            </tr>
            @endif

            <tr>
                <td colspan="6"><span class="pull-right">Grand Total</span></td>
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
$('.paid-check').change(function(){
    reCount();
});
$('.paid-amount').change(function(){
    checkPaidAmount($(this));
    reCount();
});
function checkPaidAmount(target)
{
    var max = parseInt(target.attr('max'));
    var value = parseInt(target.val());
    if(!$.isNumeric(target.val())){
        alert('harap masukkan format angka yang benar');
        target.val(max);
    }
    // if(value > max){
    //     alert('pembayaran maksimal sejumlah outstanding saat ini');
    //     target.val(max);
    // }
}
</script>
@endif