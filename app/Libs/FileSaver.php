<?php

namespace App\Libs;

use App\Models\File;
use Illuminate\Http\UploadedFile;

trait FileSaver
{
    public function saveFile(UploadedFile $file, $path, $fileableId, $fileableType, string $name): ?File
    {
        $path = $file->storeAs($path, $name);

        $uploadedFile = File::create([
            'name' => $name,
            'path' => $path,
            'fileable_id' => $fileableId,
            'fileable_type' => $fileableType,
        ]);

        return $uploadedFile;
    }
}
