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

class FileController extends Controller
{
    use FileSaver;

    public function storeFile(Request $request)
    {
        $field = [
            'file' => $request->file('file'),
            'fileable_id' => $request->fileable_id,
            'fileable_type' => $request->fileable_type,
        ];

        $rules = [
            'file' =>  ['required', 'file', 'mimes:pdf'],
            'fileable_id' => ['required', 'uuid'],
            'fileable_type' => [
                'required',
                'string',
                Rule::in([
                    'person'
                ])
            ],
        ];

        $validator = Validator::make($field, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $type = [
            'person' => [
                'model' => 'App\Models\Person',
                'path' => 'people',
            ],
        ];

        $fileable = $type[$request->fileable_type]['model']::where('id', $request->fileable_id)->first();
        if (!$fileable) return (new Response)->json(null, 'Fileable not found', 404);

        $filename = ((string) Str::uuid()) . '.' . $field['file']->getClientOriginalExtension();

        try {
            $file = $this->saveFile(
                $request->file('file'),
                $type[$request->fileable_type]['path'],
                $request->fileable_id,
                $type[$request->fileable_type]['model'],
                $filename
            );

            return (new Response)->json($file->toArray(), 'File uploaded successfully');
        } catch (\Exception $e) {
            return (new Response)->json(null, $e->getMessage(), 500);
        }
    }
}
