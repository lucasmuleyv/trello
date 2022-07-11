<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    use HasFactory;
    protected $table = 'lists';

    public function cards()
    {
        return $this->hasMany(Card::class,'list_id')->orderBy('order','ASC');
    }
}
