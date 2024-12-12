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
        return view('empresa.configuraciones.index')->with(compact([
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
        $this->authorize('update', $obj);
        if($request->hasFile('avatar')){

            $request->validate([
                'avatar' => 'mimes:jpg,jpeg,png,webp,tiff,svg|required|max:2000'
            ]);
            $imageName = Str::uuid() . '.' . $request->avatar->extension();
            $request->avatar->storeAs($imageName);            
            $obj->update(['valor' => Storage::url($imageName)]);
            toast('Imagen establecida', 'success')->autoClose(2000);
        }
        return back();
    }

    public function unset_avatar(Request $request){
        $obj = obj_variable_global('AVATAR');
        $this->authorize('update', $obj);
        if(Storage::exists(str_replace(url('/storage'), '', $obj->valor))){
            Storage::delete(str_replace(url('/storage'), '', $obj->valor));
        }
        $obj->update(['valor' => '']);
        toast('Imagen eliminada', 'success')->autoClose(2000);
        return back();
    }
}
