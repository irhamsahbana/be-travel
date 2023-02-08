<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;


class File extends Model
{
    use HasFactory, HasUuids;

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'fileable_type',
    ];

    protected $appends = [
        'public_path',
    ];

    protected $guarded = [];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function publicPath() : Attribute
    {
        return new Attribute(
            fn () => config('app.url') . Storage::url($this->path)
        );
    }
}
