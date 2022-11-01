<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\Response;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Str;

class GetCitiesController extends Controller
{
    public function provincesAndCities()
    {
        $provinces = Http::get('https://dev.farizdotid.com/api/daerahindonesia/provinsi')->json();
        $provinces = $provinces['provinsi'];
        $data = [];

        // delete all provices
        Category::where('group_by', 'cities')->forceDelete();
        Category::where('group_by', 'provinces')->forceDelete();

        foreach ($provinces as $province) {
            $data[] = [
                'id' => Str::uuid(),
                'label' => $province['nama'],
                'name' => Str::slug($province['nama']),
                'group_by' => 'provinces',
                'notes' => $province['id'],
                'created_at' => now(),
            ];
        }
        Category::insert($data);

        // get all cities
        $citiesOfProvince = [];
        foreach($provinces as $province) {
            $cities = Http::get('https://dev.farizdotid.com/api/daerahindonesia/kota?id_provinsi=' . $province['id'])->json();
            $cities = $cities['kota_kabupaten'];

            foreach ($cities as $city) {
                $citiesOfProvince[] = [
                    'id' => Str::uuid(),
                    'category_id' => Category::where('notes', $province['id'])->first()->id ?? null,
                    'label' => $city['nama'],
                    'name' => Str::slug($city['nama']),
                    'group_by' => 'cities',
                    'notes' => $city['id'],
                    'created_at' => now(),
                ];
            }
            // $citiesOfProvince[] = $cities;
        }

        Category::insert($citiesOfProvince);

        $response = new Response();
        return $response->json(Category::whereIn('group_by', ['provinces', 'cities'])->get(), 'success');
    }
}
