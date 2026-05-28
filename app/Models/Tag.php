<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    //  お問い合わせとのリレーション（多対多）
    public function contacts()
    {
        return $this->belongsToMany(Contact::class);
    }
}