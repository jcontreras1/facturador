<?php

namespace App\Http\Controllers;

use App\Models\VariableGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    public function index(){
        $variables = VariableGlobal::all();
        return view('config.index')->with(compact([
            'variables',
        ]));
    }

    public function update(VariableGlobal $config, Request $request){
        $config->update($request->all());
        toast('Variable modificada', 'success')->autoClose(2000);
        return back();
    }

    public function set_avatar(Request $request){
        $obj = obj_variable_global('AVATAR');
        if($request->hasFile('avatar')){

            $request->validate([
                'avatar' => 'mimes:jpg,jpeg,png,webp,tiff,svg|required|max:2000'
            ]);

            if ($oldAvatarPath = avatar_storage_relative_path($obj->valor)) {
                Storage::disk('public')->delete($oldAvatarPath);
            }

            $imageName = Str::uuid() . '.' . $request->avatar->extension();
            $path = $request->file('avatar')->storeAs('avatars', $imageName, 'public');
            $obj->update(['valor' => url('/storage/' . ltrim($path, '/'))]);
            toast('Imagen establecida', 'success')->autoClose(2000);
        }
        return back();
    }

    public function unset_avatar(Request $request){
        $obj = obj_variable_global('AVATAR');
        if($path = avatar_storage_relative_path($obj->valor)){
            Storage::disk('public')->delete($path);
        }
        $obj->update(['valor' => '']);
        toast('Imagen eliminada', 'success')->autoClose(2000);
        return back();
    }
}
