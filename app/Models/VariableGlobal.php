<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariableGlobal extends Model
{
    protected $table = 'variables_globales';
    protected $fillable = ['valor'];
}
