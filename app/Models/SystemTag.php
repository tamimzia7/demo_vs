<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemTag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color', 'description'];

    public function delete(): bool
    {
        return false;
    }
}
