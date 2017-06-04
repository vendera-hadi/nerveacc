<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\User;

class ProfileController extends Controller
{
	public function index(){
	    $data['users'] = User::where('id',Auth::user()->id)->first();
	    return view('profile', $data);
	}

	 public function update(Request $request){
        $this->validate($request, [
            'name' => 'required|max:255',
            'email' => 'required|max:255',
            'password' => 'required|max:255',
        ]);
        $input = $request->except(['image']);
        $updateData = $input;

        if ($request->hasFile('image')) {
            $extension = $request->image->extension();
            if(strtolower($extension)!='jpg' && strtolower($extension)!='png' && strtolower($extension)!='jpeg'){
                $request->session()->flash('error', 'Image Format must be jpg or png');
                return redirect()->back();
            }
            $image = "profile_pic_".Auth::user()->id.'.'.$extension;
            $request->file('image')->move(
                            base_path() . '/public/upload/', $image
                        );
            $updateData['image'] = $image;
        }
        if($request['password'] == 'xxx'){
                unset($updateData['password']);
        }else{
                $updateData['password'] = bcrypt($request['password']);
        }

        $id = Auth::user()->id;
        User::find($id)->update($updateData);
        $request->session()->flash('success', 'Update profile success');
        return redirect()->back();
    }
}