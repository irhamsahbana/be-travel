<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Libs\Response;
use App\Libs\FileSaver;
use Illuminate\Validation\Rule;

use App\Models\{
    Person,
    Category,
    File,
    Service
};

class FileController extends Controller
{
    use FileSaver;

    public function storeFile(Request $request)
    {
        $userCategory = $this->getUser()?->person?->category?->name;

        $field = [
            'file' => $request->file('file'),
            'fileable_id' => $request->fileable_id,
            'fileable_type' => $request->fileable_type,
        ];

        $rules = [
            'file' =>  ['required', 'file'],
            'fileable_id' => ['required', 'uuid'],
            'fileable_type' => [
                'required',
                'string',
                Rule::in([
                    'services'
                ])
            ],
        ];

        $validator = Validator::make($field, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        switch ($request->fileable_type) {
            case 'people':
                $model = Person::class;
                break;
            case 'categories':
                $model = Category::class;
                break;
            case 'services':
                $model = Service::class;
                break;
            default:
                return (new Response)->json(null, 'Fileable type not found', 404);
        }

        $fileable = $model::where('id', $request->fileable_id)->first();
        if (!$fileable) return (new Response)->json(null, 'Fileable not found', 404);

        $filename = ((string) Str::uuid()) . '.' . $field['file']->getClientOriginalExtension();

        try {
            $file = $this->saveFile(
                $fileable,
                $request->file('file'),
                $model,
                $filename,
            );

            $file = $file ? $file->toArray() : null;

            return (new Response)->json($file, 'File uploaded successfully');
        } catch (\Exception $e) {
            return (new Response)->json(null, $e->getMessage(), 500);
        }
    }
}
