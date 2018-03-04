<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

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

	public function users(){
		$data['users'] = User::select('users.id','users.name','roles.id as role_id','roles.name as role')->leftJoin('user_has_roles','users.id','=','user_has_roles.user_id')
							->leftJoin('roles','user_has_roles.role_id','=','roles.id')->paginate(20);
		$data['first_superadmin'] = \DB::table('user_has_roles')->where('role_id',1)->orderBy('user_id')->first();
		$data['first_superadmin'] = @$data['first_superadmin']->user_id;
		$data['roles'] = Role::all();
		return view('acl_user',$data);
	}

	public function usersInsert(Request $request){
		$name = $request->name;
		$email = $request->email;
		$password = $request->password;
		$role_id = $request->role_id;

		// cek email
		$cek = User::where('email',$email)->first();
		if(!empty($cek)) return response()->json(['errorMsg'=> 'Email sudah terdaftar, coba ganti dengan email lain']);

		// insert
		$newuser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);
        \DB::table('user_has_roles')->insert(['user_id' => $newuser->id, 'role_id' => $role_id]);

		\Session::flash('success', 'Insert Success');
		return response()->json(['success'=>1]);
	}

	public function usersUpdate(Request $request){
		$name = $request->name;
		$email = $request->email;
		$password = $request->password;
		$role_id = $request->role_id;
		$id = $request->user_id;

		$current = User::find($id);
		// cek email
		$cek = User::where('email',$email)->where('email','!=',$current->email)->first();
		if(!empty($cek)) return response()->json(['errorMsg'=> 'Email sudah terdaftar, coba ganti dengan email lain']);

		// update
		$current->name = $name;
		$current->email = $email;
		if(!empty($password)) $current->password = bcrypt($password);
		$current->save();

		$cekrole = \DB::table('user_has_roles')->where('user_id',$id)->where('role_id',$role_id)->first();
		if(empty($cekrole)){
			\DB::table('user_has_roles')->where('user_id',$id)->delete();
			\DB::table('user_has_roles')->insert(['user_id' => $id, 'role_id' => $role_id]);
		}
		\Session::flash('success', 'Update Success');
		return response()->json(['success'=>1]);
	}

	public function usersDetail(Request $request){
		try{
			$id = $request->id;
			$result = [];
			$result = User::select('users.id','users.name','users.email','roles.id as role_id','roles.name as role')->leftJoin('user_has_roles','users.id','=','user_has_roles.user_id')
							->leftJoin('roles','user_has_roles.role_id','=','roles.id')->where('users.id',$id)->first();
			return response()->json(['success'=>1, 'data' => $result]);
		}catch(\Exception $e){
			return response()->json(['errorMsg'=>$e->getMessage()]);
		}
	}

	public function usersDelete(Request $request){
		try{
			$id = $request->id;
			User::find($id)->delete();
			\DB::table('user_has_roles')->where('user_id',$id)->delete();
			\Session::flash('success', 'Delete Success');
			return response()->json(['success'=>1]);
		}catch(\Exception $e){
			return response()->json(['errorMsg'=>$e->getMessage()]);
		}
	}
}