<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $table = 'recordings';

    protected $fillable = [

        'class_id',

        'topic',

        'duration',

        'video_link'
    ];

    /*
    |--------------------------------------------------------------------------
    | RECORDING BELONGS TO CLASS
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(

            ClassModel::class,

            'class_id'
        );
    }
}