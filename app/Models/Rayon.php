<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Rayon extends Model {
    protected $table = 'ms_rayon';
    protected $fillable = [
        'rayon_code',
        'rayon_name',
        'blok_id',
        'wilayah_id',
        'cabang_id',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    protected $primaryKey = 'rayon_id';
    public $timestamps = true;
}
