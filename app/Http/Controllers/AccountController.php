<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use App\Models\Role;
use App\Models\Permission;

class AccountController extends Controller
{
	public function roles(){
		$data['roles'] = Role::paginate(20);
		$data['permissions'] = Permission::all();
		return view('roles',$data);
	}

	public function rolesInsert(Request $request){
		$name = $request->name;
		$role = Role::create(['name' => $name]);

		// create permissions
		$permissions = $request->permission;
		if(count($permissions) > 0){
			foreach ($permissions as $prm) {
				\DB::table('role_has_permissions')->insert(['permission_id'=>$prm, 'role_id'=>$role->id]);
			}
		}

		\Session::flash('success', 'Insert Success');
		return redirect('roles');
	}

	public function rolesUpdate(Request $request, $id){
		$name = $request->name;
		Role::find($id)->update(['name' => $name]);
		// delete yg lama
		\DB::table('role_has_permissions')->where('role_id',$id)->delete();
		// create permissions
		$permissions = $request->permission;
		if(count($permissions) > 0){
			foreach ($permissions as $prm) {
				\DB::table('role_has_permissions')->insert(['permission_id'=>$prm, 'role_id'=>$id]);
			}
		}
		\Session::flash('success', 'Update Success');
		return redirect('roles');
	}

	public function rolesDelete(Request $request){
		try{
			$id = $request->id;
			Role::find($id)->delete();
			// delete permission jg
			\DB::table('role_has_permissions')->where('role_id',$id)->delete();
			\Session::flash('success', 'Delete Success');
			return response()->json(['success'=>1]);
		}catch(\Exception $e){
			return response()->json(['errorMsg'=>$e->getMessage()]);
		}
	}

	public function rolesDetail(Request $request){
		try{
			$id = $request->id;
			$result = [];
			$role = Role::find($id);
			$result['name'] = $role->name;
			$result['permissions'] = [];
			// get permission dr role
			$permissions = \DB::table('role_has_permissions')->where('role_id',$id)->get();
			if(count($permissions) > 0){
				foreach ($permissions as $prm) {
					$result['permissions'][] = $prm->permission_id;
				}
			}
			return response()->json(['success'=>1, 'data' => $result]);
		}catch(\Exception $e){
			return response()->json(['errorMsg'=>$e->getMessage()]);
		}
	}
}