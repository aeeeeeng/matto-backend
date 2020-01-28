<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'product_types';
    protected $fillable = ['code', 'name', 'status', 'created_by', 'updated_by'];

    public static function rule()
    {
        $rules = [
            'name' => 'required|string|max:255|unique:product_types'
        ];
        return $rules;
    }

    public static function ruleUpdate($id)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:product_types,name,' . $id
        ];
        return $rules;
    }
}
