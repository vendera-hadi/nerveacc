<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Kartu Aktiva Komersial - {{$asset->name}}</h4>
</div>

<div class="modal-body" style="padding: 20px 40px">
@php
$percentage = $asset->assetType->saldo_menurun;
$price = $asset->price;
$nilai_sisa = $price;
$tgl_perolehan = $asset->date;
$count = 0;
$tempYear = 0;
$year = date('Y', strtotime($tgl_perolehan));
$lastest_year = date('Y', strtotime($tgl_perolehan)) + $asset->assetType->masa_manfaat;
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
        @while ($year <= $lastest_year)
        @php
          $year = date('Y', strtotime("+$count month",strtotime($tgl_perolehan)));
          if($year > $lastest_year) break;
          if($tempYear != $year){
            $depPerMonth = $asset->depreciationPerMonthCustom('SALDO MENURUN',$year);
            $tempYear = $year;
          }
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