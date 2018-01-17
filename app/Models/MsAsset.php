<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MsAsset extends Model
{
   protected $table ='ms_assets';
   protected $fillable =['name','ms_asset_type_id','depreciation_type','date','price'];
   public $timestamps  = false;

   public function assetType()
   {
      return $this->belongsTo('App\Models\MsAssetType','ms_asset_type_id');
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
