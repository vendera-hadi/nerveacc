<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// load model
use App\Models\MsMasterCoa;
use App\Models\MsAsset;
use App\Models\MsAssetType;
use App\Models\MsSupplier;
use App\Models\MsGroupAccount;
use App\Models\TrAssetMutation;
use App\Models\MsPerawatanAsset;
use App\Models\MsAsuransiAsset;

use Auth;
use DB;

class FixedAssetController extends Controller
{
  public function index()
  {
    $data = [];
    return view('assets2',$data);
  }

  public function indexTypes()
  {
    $data = [];
    return view('assets-group',$data);
  }

  public function get(Request $request){
        try{
            // params
            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            // olah data
            $count = MsAsset::count();
            $fetch = MsAsset::with('assetType');
            if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
                    // tentuin operator
                    switch ($filter->op) {
                        case 'contains':
                            $op = 'like';
                            break;
                        case 'less':
                            $op = '<=';
                            break;
                        case 'greater':
                            $op = '>=';
                            break;
                        default:
                            break;
                    }
                    // end special condition
                    if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    else $fetch = $fetch->where($filter->field, $op, $filter->value);
                }
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['name'] = $value->name;
                $temp['date'] = date('d F Y',strtotime($value->date));
                $temp['price'] = "IDR ".number_format($value->price,0);
                // $temp['depreciation_type'] = $value->depreciation_type;
                $temp['jenis_harta'] = $value->assetType->jenis_harta;
                $temp['kelompok_harta'] = $value->assetType->kelompok_harta;
                $temp['masa_manfaat'] = $value->assetType->masa_manfaat." tahun";

                // $temp['nilai_sisa'] = "IDR ".number_format($value->nilaiSisaTahunan(date('Y')), 0);
                // $temp['per_month'] = "IDR ".number_format($value->depreciationPerMonth(date('Y')), 0);
                // $temp['per_year'] = "IDR ".number_format($value->depreciationPerYear(date('Y')), 0);

                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function getTypes(Request $request)
    {
      try{
            // params
            $page = $request->page;
            $perPage = $request->rows;
            $page-=1;
            $offset = $page * $perPage;
            // @ -> isset(var) ? var : null
            $sort = @$request->sort;
            $order = @$request->order;
            $filters = @$request->filterRules;
            if(!empty($filters)) $filters = json_decode($filters);

            // olah data
            $count = MsAssetType::count();
            $fetch = MsAssetType::query();
            if(!empty($filters) && count($filters) > 0){
                foreach($filters as $filter){
                    $op = "like";
                    // tentuin operator
                    switch ($filter->op) {
                        case 'contains':
                            $op = 'like';
                            break;
                        case 'less':
                            $op = '<=';
                            break;
                        case 'greater':
                            $op = '>=';
                            break;
                        default:
                            break;
                    }
                    // end special condition
                    if($op == 'like') $fetch = $fetch->where(\DB::raw('lower(trim("'.$filter->field.'"::varchar))'),$op,'%'.$filter->value.'%');
                    else $fetch = $fetch->where($filter->field, $op, $filter->value);
                }
            }
            $count = $fetch->count();
            if(!empty($sort)) $fetch = $fetch->orderBy($sort,$order);
            $fetch = $fetch->skip($offset)->take($perPage)->get();
            $result = ['total' => $count, 'rows' => []];
            foreach ($fetch as $key => $value) {
                $temp = [];
                $temp['id'] = $value->id;
                $temp['jenis_harta'] = $value->jenis_harta;
                $temp['kelompok_harta'] = $value->kelompok_harta;
                $temp['masa_manfaat'] = $value->masa_manfaat." tahun";
                $temp['garis_lurus'] = ($value->garis_lurus * 100)." %";
                $temp['saldo_menurun'] = ($value->saldo_menurun * 100)." %";
                $temp['custom_rule'] = !empty($value->custom_rule) ? ($value->custom_rule * 100)." %" : "-";

                $result['rows'][] = $temp;
            }
            return response()->json($result);
        }catch(\Exception $e){
            return response()->json(['errorMsg' => $e->getMessage()]);
        }
    }

    public function add(Request $request)
    {
        $coaYear = date('Y');
        $data['title'] = 'Add';
        $data['kelompok_harta'] = MsAssetType::all();
        $data['suppliers'] = MsSupplier::all();
        $data['group_accounts'] = MsGroupAccount::all();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['action'] = route('fixed_asset.insert');
        return view('assets-form',$data);
    }

    public function edit(Request $request)
    {
        $coaYear = date('Y');
        $data['title'] = 'Edit';
        $data['kelompok_harta'] = MsAssetType::all();
        $data['suppliers'] = MsSupplier::all();
        $data['group_accounts'] = MsGroupAccount::all();
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        $data['detail'] = MsAsset::find($request->id);
        $data['action'] = route('fixed_asset.update', ['id' => $request->id]);
        return view('assets-form',$data);
    }

    public function insert(Request $request)
    {
        $input = $request->all();
        if ($request->hasFile('image')) {
            $extension = $request->image->extension();
            if(strtolower($extension)!='jpg' && strtolower($extension)!='png' && strtolower($extension)!='jpeg'){
                $request->session()->flash('error', 'Image Format must be jpg or png');
                return redirect()->back();
            }
            $newname = "asset-".microtime().'.'.$extension;
            $path = $request->image->move(public_path('upload'), $newname);
            // dd($path);
            $input['image'] = $newname;
        }
        $asset = MsAsset::create($input);
        $inputMutasi = $request->only(['kode_induk','cabang','lokasi','area','departemen','user','kondisi']);
        $inputMutasi['asset_id'] = $asset->id;
        TrAssetMutation::create($inputMutasi);
        return redirect()->back()->with('success','Insert success');
    }

    public function update(Request $request)
    {
        $input = $request->all();
        $data = MsAsset::find($request->id);
        if($data->kode_induk != @$request->kode_induk ||
            $data->cabang != @$request->cabang ||
            $data->lokasi != @$request->lokasi ||
            $data->area != @$request->area ||
            $data->departemen != @$request->departemen ||
            $data->user != @$request->user ||
            $data->kondisi != @$request->kondisi
        ){
            // insert mutasi
            $inputMutasi = $request->only(['kode_induk','cabang','lokasi','area','departemen','user','kondisi']);
            $inputMutasi['asset_id'] = $data->id;
            TrAssetMutation::create($inputMutasi);
        }
        if ($request->hasFile('image')) {
            $extension = $request->image->extension();
            if(strtolower($extension)!='jpg' && strtolower($extension)!='png' && strtolower($extension)!='jpeg'){
                $request->session()->flash('error', 'Image Format must be jpg or png');
                return redirect()->back();
            }
            $newname = "asset-".microtime().'.'.$extension;
            $path = $request->image->move(public_path('upload'), $newname);
            // dd($path);
            $input['image'] = $newname;
        }
        $data->update($input);
        return redirect()->back()->with('success','Update success');
    }

    public function delete(Request $request)
    {
        return MsAssetType::destroy($request->id);
    }

    public function addTypes()
    {
        $coaYear = date('Y');
        $data['title'] = "Add";
        $data['action'] = route('fixed_asset.type.insert');
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        return view('assets-group-form',$data);
    }

    public function insertTypes(Request $request)
    {
        $input = $request->all();
        $input = $this->countPercentageDepreciation($input);
        $input['custom_rule'] = $input['custom_rule'] / 100;
        MsAssetType::insert($input);
        return redirect()->back()->with('success','Insert success');
    }

    public function editTypes(Request $request)
    {
        $coaYear = date('Y');
        $data['title'] = "Edit";
        $data['detail'] = MsAssetType::find($request->id);
        $data['action'] = route('fixed_asset.type.update', ['id' => $request->id]);
        $data['accounts'] = MsMasterCoa::where('coa_year',$coaYear)->where('coa_isparent',0)->orderBy('coa_type')->get();
        return view('assets-group-form',$data);
    }

    public function updateTypes(Request $request)
    {
        $input = $request->all();
        $input = $this->countPercentageDepreciation($input);
        $input['custom_rule'] = $input['custom_rule'] / 100;
        $data = MsAssetType::find($request->id);
        $data->update($input);
        return redirect()->back()->with('success','Update success');
    }

    public function deleteTypes(Request $request)
    {
        return MsAssetType::destroy($request->id);
    }

    private function countPercentageDepreciation($input)
    {
        $persentase_lurus = 100 / $input['masa_manfaat'];
        $input['garis_lurus'] = $persentase_lurus / 100;
        $input['saldo_menurun'] = $persentase_lurus * 2 / 100;
        return $input;
    }

    public function fiskal(Request $request)
    {
        $id = $request->id;
        $data['asset'] = MsAsset::find($id);
        return view('assets-fiskal', $data);
    }

    public function custom(Request $request)
    {
        $id = $request->id;
        $data['asset'] = MsAsset::find($id);
        return view('assets-custom', $data);
    }

    public function komersial(Request $request)
    {
        $id = $request->id;
        $data['asset'] = MsAsset::find($id);
        return view('assets-komersial', $data);
    }

    public function mutasi(Request $request)
    {
        $id = $request->id;
        $data['asset'] = MsAsset::find($id);
        return view('assets-mutasi', $data);
    }

    public function perawatan(Request $request)
    {
        $id = $request->id;
        $data['asset'] = MsAsset::find($id);
        return view('assets-perawatan', $data);
    }

    public function getPerawatan(Request $request)
    {
        $id = $request->id;
        $data = MsPerawatanAsset::find($id);
        return $data;
    }

    public function insertPerawatan(Request $request)
    {
        $input = $request->except(['type','id']);
        MsPerawatanAsset::create($input);
        return response()->json(['success'=>true]);
    }

    public function updatePerawatan(Request $request)
    {
        $input = $request->except(['type','id']);
        $data = MsPerawatanAsset::find($request->id);
        $data->update($input);
        return response()->json(['success'=>true]);
    }

    public function deletePerawatan(Request $request)
    {
        MsPerawatanAsset::destroy($request->id);
        return response()->json(['success'=>true]);
    }

    public function asuransi(Request $request)
    {
        $id = $request->id;
        $data['asset'] = MsAsset::find($id);
        return view('assets-asuransi', $data);
    }

    public function getAsuransi(Request $request)
    {
        $id = $request->id;
        $data = MsAsuransiAsset::find($id);
        return $data;
    }

    public function insertAsuransi(Request $request)
    {
        $input = $request->except(['type','id']);
        MsAsuransiAsset::create($input);
        return response()->json(['success'=>true]);
    }

    public function updateAsuransi(Request $request)
    {
        $input = $request->except(['type','id']);
        $data = MsAsuransiAsset::find($request->id);
        $data->update($input);
        return response()->json(['success'=>true]);
    }

    public function deleteAsuransi(Request $request)
    {
        MsAsuransiAsset::destroy($request->id);
        return response()->json(['success'=>true]);
    }

    public function report()
    {

    }

}