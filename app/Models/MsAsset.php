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

   public function depreciationPerMonth($year)
   {
      switch($this->attributes['depreciation_type']) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          return number_format($persentase * $price / 12, 0);
          break;
        case 'SALDO MENURUN':
          $startTime = Carbon::parse($this->attributes['date']);
          $finishTime = Carbon::now();
          $yearGap = $finishTime->diffInYears($startTime);
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          return number_format($depreciation / 12, 0);
          break;
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          return number_format($persentase * $price / 12, 0);
          break;
      }
   }

   public function depreciationPerYear($year)
   {
      switch($this->attributes['depreciation_type']) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          return number_format($persentase * $price, 0);
          break;
        case 'SALDO MENURUN':
          $startTime = Carbon::parse($this->attributes['date']);
          $finishTime = Carbon::now();
          $yearGap = $finishTime->diffInYears($startTime);
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          return number_format($depreciation, 0);
          break;
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          return number_format($persentase * $price, 0);
          break;
      }
   }

   public function nilaiSisaTahunan($year)
   {
      $startTime = Carbon::parse($this->attributes['date']);
      $finishTime = Carbon::now();
      $yearGap = $finishTime->diffInYears($startTime);
      switch($this->attributes['depreciation_type']) {
        case 'GARIS LURUS':
          $persentase = $this->assetType->garis_lurus;
          $price = $this->attributes['price'];
          $depreciation = $yearGap * $persentase * $price;
          $nilai_sisa = $price - $depreciation;
          if($nilai_sisa < 0) $nilai_sisa = 0;
          return number_format($nilai_sisa, 0);
          break;
        case 'SALDO MENURUN':
          $startTime = Carbon::parse($this->attributes['date']);
          $finishTime = Carbon::now();
          $yearGap = $finishTime->diffInYears($startTime);
          $persentase = $this->assetType->saldo_menurun;
          $price = $this->attributes['price'];
          $temp = $price;
          for ($i=0; $i < $yearGap; $i++) {
            $depreciation = $temp * $persentase;
            $temp = $temp - $depreciation;
          }
          if($temp < 0) $temp = 0;
          return number_format($temp, 0);
        case 'CUSTOM':
          $persentase = $this->assetType->custom_rule;
          $price = $this->attributes['price'];
          $depreciation = $yearGap * $persentase * $price;
          $nilai_sisa = $price - $depreciation;
          if($nilai_sisa < 0) $nilai_sisa = 0;
          return number_format($nilai_sisa, 0);
          break;
      }
   }
}
