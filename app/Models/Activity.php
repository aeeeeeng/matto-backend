<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['subject', 'url', 'method', 'ip', 'agent', 'user_id', 'count_data', 'status'];
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
