<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['owner_id', 'name', 'share_code'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }
}
