<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Kartu Aktiva Fiskal - {{$asset->name}}</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
@php
$percentage = $asset->assetType->garis_lurus;
$price = $asset->price;
$nilai_sisa = $price;
$tgl_perolehan = $asset->date;
$count = 0;
@endphp
<p>Masa manfaat <b>{{$asset->assetType->masa_manfaat}} tahun</b></p>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Tgl</th>
            <th>Keterangan</th>
            <th>Kelompok Harta</th>
            <th>Harga Perolehan</th>
            <th>Penyusutan</th>
            <th>Saldo</th>
        </tr>

    </thead>
    <tbody>
        <tr>
            <td>{{date('d/m/Y',strtotime($tgl_perolehan))}}</td>
            <td>Perolehan {{$asset->name}}</td>
            <td>{{$asset->assetType->kelompok_harta}}</td>
            <td>IDR {{number_format($price,0)}}</td>
            <td>0</td>
            <td>IDR {{number_format($price,0)}}</td>
        </tr>
        @while ($nilai_sisa > 0)
        @php
          $depPerMonth = $asset->depreciationPerMonthCustom('GARIS LURUS', date('Y', strtotime("+$count month",strtotime($tgl_perolehan))));
          $nilai_sisa -= $depPerMonth;
          if($nilai_sisa < 0) $nilai_sisa = 0;
        @endphp
        <tr @if(date('m',strtotime("+$count month",strtotime($tgl_perolehan))) == date('m',strtotime("-1 month")) && date('Y',strtotime("+$count month",strtotime($tgl_perolehan))) == date('Y',strtotime("-1 month"))) style="background-color: #e3a6a6" @endif>
            <td>{{date('t/m/Y',strtotime("+$count month",strtotime($tgl_perolehan)))}}</td>
            <td>Penyusutan Periode {{date('F Y', strtotime("+$count month",strtotime($tgl_perolehan)))}}</td>
            <td>{{$asset->assetType->kelompok_harta}}</td>
            <td>IDR 0</td>
            <td>(IDR {{number_format($depPerMonth,0)}})</td>
            <td>IDR {{number_format($nilai_sisa,0)}}</td>
        </tr>
        @php
          $count++;
        @endphp
        @endwhile
    </tbody>
</table>
</div>