<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(){
        $items = Item::orderBy('descripcion')->paginate(15);
        return view('items.index')->with(compact('items'));
    }

    public function store(StoreItemRequest $request){
        Item::create($request->validated());
        toast('Item creado correctamente', 'success');
        return redirect()->route('items.index');
    }

    public function destroy(Item $item){
        $item->delete();
        toast('Item eliminado correctamente', 'success');
        return redirect()->route('items.index');
    }
}
