<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{
    protected $table = 'uom';
    protected $fillable = ['code', 'full_name', 'name', 'status', 'created_by', 'updated_by'];

    public static function rule()
    {
        $rules = [
            'name' => 'required|string|max:2|unique:uom',
            'full_name' => 'required|string|max:255|unique:uom',
        ];
        return $rules;
    }

    public static function ruleUpdate($id)
    {
        $rules = [
            'name' => 'required|string|max:2|unique:uom,name,' . $id,
            'full_name' => 'required|string|max:255|unique:uom,full_name,' . $id,
        ];
        return $rules;
    }

}
