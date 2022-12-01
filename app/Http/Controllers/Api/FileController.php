<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Libs\Response;
use App\Libs\FileSaver;

class FileController extends Controller
{
    use FileSaver;

    public function storeFile(Request $request)
    {
        $field = [
            'file' => $request->file('file'),
            'fileable_id' => $request->fileable_id,
        ];

        $rules = [
            'file' =>  ['required', 'file', 'mimes:pdf'],
            'fileable_id' => ['required', 'uuid'],
        ];

        $validator = Validator::make($field, $rules);
        if ($validator->fails()) return (new Response)->json(null, $validator->errors(), 422);

        $tables = [
            'people' => [
                'model' => 'App\Models\Person',
                'path' => 'people',
            ],
        ];

        foreach ($tables as $table => $data) {
            $model = $data['model'];
            $path = $data['path'];

            $fileable = DB::table($table)->where('id', $field['fileable_id'])->first();
            if ($fileable) {
                $filename = ((string) Str::uuid()) . '.' . $field['file']->getClientOriginalExtension();

                $this->saveFile($field['file'], $path, $field['fileable_id'], $model, $filename);
                return (new Response)->json(null, 'uploaded successfully', 200);
            }
        }

        return (new Response)->json(null, 'fileable_id not found', 404);
    }
}
