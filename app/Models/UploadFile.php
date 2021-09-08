<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadFile extends Model
{
    protected $fillable = [
        'password',
        'link',
        'filename',
        'folder',
        'link_expire',
    ];

    public function getByLink(string $link)
    {
        return UploadFile::where('link', $link)->first();
    }
}
