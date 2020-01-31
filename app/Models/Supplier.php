<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{

    protected $fillable = ['code', 'name', 'full_name', 'address', 'image_dir', 'image', 'status', 'created_by', 'updated_by'];

    public static function rule()
    {
        $rules = [
            'name' => 'required|string|max:255|',
            'full_name' => 'required|string|max:255|unique:suppliers',
            'address' => 'required|string|max:255|',
            'image_upload' => 'mimes:jpeg,jpg,png,gif|max:30000'
        ];
        return $rules;
    }

    public static function ruleUpdate($id)
    {
        $rules = [
            'name' => 'required|string|max:255|',
            'full_name' => 'required|string|max:255|unique:suppliers,full_name,' . $id,
            'address' => 'required|string|max:255|',
            'image_upload' => 'mimes:jpeg,jpg,png,gif|max:30000'
        ];
        return $rules;
    }

}
