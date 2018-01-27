<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MsAsset extends Model
{
   protected $table ='ms_assets';
   protected $fillable =['name','ms_asset_type_id','depreciation_type','date','price','group_account_id','aktiva_coa_code','supplier_id','po_no','kode_induk','cabang','lokasi','area','departemen','user','kondisi','keterangan','image'];
   public $timestamps  = false;

   public function assetType()
   {
      return $this->belongsTo('App\Models\MsAssetType','ms_asset_type_id');
   }

   public function mutasi()
   {
      return $this->hasMany('App\Models\TrAssetMutation','asset_id');
   }

   public function perawatan()
   {
      return $this->hasMany('App\Models\MsPerawatanAsset','asset_id');
   }

   public function asuransi()
   {
      return $this->hasMany('App\Models\MsAsuransiAsset','asset_id');
   }

   public function depreciationPerMonthCustom($type, $year)
   {
      switch($type) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          return $persentase * $price / 12;
          break;
        case 'SALDO MENURUN':
          $startTime = Carbon::parse($this->attributes['date']);
          $yearGap = $year - date('Y',strtotime($this->attributes['date'])) + 1;
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          $depreciation = 0;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          return $depreciation / 12;
          break;
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          return $persentase * $price / 12;
          break;
      }
   }

   public function depreciationPerMonth($year, $customFinishTime = null)
   {
      switch($this->attributes['depreciation_type']) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          return $persentase * $price / 12;
          break;
        case 'SALDO MENURUN':
          $startTime = Carbon::parse($this->attributes['date']);
          if(!empty($customFinishTime)) $finishTime = Carbon::parse($customFinishTime);
          else $finishTime = Carbon::now();
          $yearGap = $finishTime->diffInYears($startTime);
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          return $depreciation / 12;
          break;
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          return $persentase * $price / 12;
          break;
      }
   }

   public function depreciationPerYear($year, $customFinishTime = null)
   {
      switch($this->attributes['depreciation_type']) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          return $persentase * $price;
          break;
        case 'SALDO MENURUN':
          $startTime = Carbon::parse($this->attributes['date']);
          if(!empty($customFinishTime)) $finishTime = Carbon::parse($customFinishTime);
          else $finishTime = Carbon::now();
          $yearGap = $finishTime->diffInYears($startTime);
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          return $depreciation;
          break;
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          return $persentase * $price;
          break;
      }
   }

   public function nilaiSisaTahunan($year, $customFinishTime = null)
   {
      $startTime = Carbon::parse($this->attributes['date']);
      if(!empty($customFinishTime)) $finishTime = Carbon::parse($customFinishTime);
      else $finishTime = Carbon::now();
      $yearGap = $finishTime->diffInYears($startTime);
      switch($this->attributes['depreciation_type']) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          $depreciation = $yearGap * $persentase * $price;
          $nilai_sisa = $price - $depreciation;
          if($nilai_sisa < 0) $nilai_sisa = 0;
          return $nilai_sisa;
          break;
        case 'SALDO MENURUN':
          $yearGap = $finishTime->diffInYears($startTime);
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          if($temp < 0) $temp = 0;
          return $temp;
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          $depreciation = $yearGap * $persentase * $price;
          $nilai_sisa = $price - $depreciation;
          if($nilai_sisa < 0) $nilai_sisa = 0;
          return $nilai_sisa;
          break;
      }
   }
}
