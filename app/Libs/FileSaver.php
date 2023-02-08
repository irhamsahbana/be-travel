<?php

namespace App\Libs;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\{
    Category,
    File,
    Person,
    Service,
};


trait FileSaver
{
    public function saveFile(Model $fileable, UploadedFile $file, $fileableType, string $name): ?File
    {
        $uploadedFile = null;

        if($fileable instanceof Category) {
            $storePath = 'categories';

            switch ($fileable->group_by) {
                case 'x':
                    break;
                case 'y':
                    break;
                case 'z':
                    break;
                default:
                    break;
            }
        } else if($fileable instanceof Person) {
            $storePath = 'people';

        } else if($fileable instanceof Service) {
            $storePath = 'public/services';

            $field = ['file' => $file];
            $rules = ['file' => ['required', 'file', 'mimes:jpeg,png,jpg', 'max:2048']];

            $validator = Validator::make($field, $rules);
            if ($validator->fails()) throw new \Exception($validator->errors()->first());

            $fileExist = $fileable->file;
            if ($fileExist) {
                $uploadedFile = $this->updateFile($storePath, $name, $fileable->id, $fileableType, $fileExist, $file);
            } else {
                $uploadedFile = $this->createFile($file, $storePath, $name, $fileable->id, $fileableType);
            }
        } else {
            return null;
        }

        return $uploadedFile;
    }

    private function createFile(UploadedFile $file, string $storePath, string $name, string $fileableId, string $fileableType) : File
    {
        $path = $file->storeAs($storePath, $name);
        $file = File::create([
            'name' => $name,
            'path' => $path,
            'fileable_id' => $fileableId,
            'fileable_type' => $fileableType,
        ]);

        return $file;
    }

    private function deleteFile(File $file)
    {
        $oldPath = $file->path;

        if (Storage::exists($oldPath)) Storage::delete($oldPath);
        $file->delete();
    }

    private function updateFile($storePath, $name, $fileableId, $fileableType, File $fileable, UploadedFile $file) : File
    {
        $this->deleteFile($fileable);
        $uploadedFile = $this->createFile($file, $storePath, $name, $fileableId, $fileableType);

        return $uploadedFile;
    }
}
