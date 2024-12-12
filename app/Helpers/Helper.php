<?php

use App\Models\VariableGlobal;

function variable_global($clave){
	if(VariableGlobal::select('valor')->where('clave', $clave)->count() > 0 ){
		return VariableGlobal::select('valor')->where('clave', $clave)->first()->valor;
	}else{
		return '';
	}
}
function obj_variable_global($clave){
	if(VariableGlobal::select('valor')->where('clave', $clave)->count() > 0 ){
		return VariableGlobal::where('clave', $clave)->first();
	}
	return false;
}

function pesosargentinos($importe){
	return number_format($importe, 2, ',', '.');
}