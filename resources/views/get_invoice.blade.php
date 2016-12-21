<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th width="100">No.Invoice</th>
                <th width="100">Unit</th>  
                <th width="50">Tgl Invoice</th>
                <th width="50">Jatuh Tempo</th>
                <th width="80">Outstanding Amount</th> 
                <th width="80" ></th> 
            </tr>
        </thead>
        <tbody>
            <?php
                if(!empty($invoice_data)){
                    foreach ($invoice_data as $key => $value) {
                        $inv_duedate = strtotime($value['inv_duedate']);
                        $inv_date = strtotime($value['inv_date']);
                        $inv_id = $value['id'];

                        $now_time = strtotime(date('Y-m-d'));

                        $class = '';
                        if($inv_duedate < $now_time ){
                            $class = 'danger';
                        }
            ?>
            <tr class="<?php echo $class;?>">
                <td><?php echo $value['inv_number'];?></td>
                <td><?php echo sprintf('%s %s', $value['unit_name'], $value['floor_name']);?></td>
                <td><?php echo date('d/m/y', $inv_date);?></td>
                <td><?php echo date('d/m/y', $inv_duedate);?></td>
                <td><?php echo 'Rp. '.$value['inv_outstanding'];?></td>
                <td>
                    <div class="input-group">
                        <div class="input-group-addon">Rp</div>
                        <input type="text" name="data_payment[invpayd_amount][<?php echo $inv_id;?>]" class="form-control" value="<?php echo $value['inv_outstanding'];?>">
                    </div>
                </td>
            </tr>
            <?php
                    }
                }else{
            ?>
            <tr>
                <td colspan="6">Data not found</td>
            </tr>
            <?php
                }
            ?>
        </tbody>
    </table>
</div>