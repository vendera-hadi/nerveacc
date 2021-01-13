<p><b>Pilih Pembayaran SP yang mana</b></p>
<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th width="10" ></th>
                <th width="100">Reminder No</th>
                <th width="100">No.Unit</th>
                <th width="50">Tgl SP</th>
                <th width="50">Jenis SP</th>
                <th width="80">Denda Amount</th>
                <th width="80">Print</th>
                <th width="100">Terbayar</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($invoice_data))
                @foreach ($invoice_data as $key => $value)
                    <?php
                        $denda_date = strtotime($value->reminder_date);
                        $inv_id = $value->id;
                    ?>
                <tr>
                    <td><input type="checkbox" name="data_payment[invpayd_amount][<?php echo $inv_id;?>]" value="<?php echo $value->denda_total;?>" class="paid-check"></td>
                    <td><?php echo $value->reminder_no;?></td>
                    <td><?php echo $value->unit_code;?></td>
                    <td><?php echo date('d/m/y', $denda_date);?></td>
                    <td><?php echo ($value->sp_type == 4 ? 'SP 1' : ($value->sp_type ==  5 ? 'SP 2' : 'SP 3'));;?></td>
                    <td><?php echo 'Rp. '.number_format($value->denda_total);?></td>
                    <td><center><a href="<?php echo url('invoice/print_manualreminder?id='.$value->id) ?>" class="print-window2" data-width="640" data-height="660">Print</a></center></td>
                    <td><input type="number" name="data_payment[totalpay][{{$inv_id}}]" value="{{floor($value->denda_total)}}"  min="1" placeholder="Jumlah Bayar / Total Paid" class="form-control paid-amount" disabled=""></td>
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="8">Data not found</td>
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

$('.print-window2').click(function(){

    var self = $(this);
    var url2 = self.attr('href');
    var title2 = self.attr('title');
    var w2 = self.attr('data-width');
    var h2 = self.attr('data-height');

    openWindow(url2, title2, w2, h2);
    return false;
});

</script>
@endif