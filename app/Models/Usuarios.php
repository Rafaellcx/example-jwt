<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuarios extends Model
{
    use SoftDeletes;

    public $table = 'usuarios';

    protected $primaryKey = 'id';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'cpf',
        'nome',
        'email',
        'senha',
        'token',
        
    ];

    protected $casts = [
        'id'    => 'integer',
        'cpf'   => 'string',
        'nome'  => 'string',
        'email' => 'string',
        'senha' => 'string',
        'token' => 'string',
        
    ];
}
