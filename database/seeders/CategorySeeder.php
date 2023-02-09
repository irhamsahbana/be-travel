<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


use App\Models\{
    Category,
};

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvList = [
            storage::disk('database')->path('csv/categories.csv'),
            storage::disk('database')->path('csv/company-categories.csv'),
            storage::disk('database')->path('csv/provinces-cities.csv'),
        ];

        foreach ($csvList as $csvFilePath) {
            dump($csvFilePath);
            $this->generics($csvFilePath);
        }

        // $jsonList = [
        //     storage::disk('database')->path('json/banks.json'),
        // ];

        // foreach ($jsonList as $jsonFilePath) {
        //     dump($jsonFilePath);
        //     $this->jsonGenerics($jsonFilePath);
        // }
    }

    public function generics($path)
    {
        if (!file_exists($path)) return;

        $csv = new \ParseCsv\Csv();
        $csv->auto($path);

        $categories =  $csv->data;

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'id' => !empty($category['ID']) ? $category['ID'] : Str::uuid()->toString(),
                ],
                [
                    'category_id' => $category['CATEGORY_ID'] == "" ? null : $category['CATEGORY_ID'],
                    "company_id" => $category['COMPANY_ID'] == "" ? null : $category['COMPANY_ID'],
                    'name' => $category['NAME'],
                    'group_by' => $category['GROUP_BY'],
                    'label' => $category['LABEL'],
                    'notes' => $category['NOTES'] == "" ? null : $category['NOTES'],
                ]
            );
        }
    }

    public function jsonGenerics($path)
    {
        if (!file_exists($path)) return;

        $json = json_decode(file_get_contents($path), true);
        dump($json);

        foreach ($json as $category) {
            Category::updateOrCreate(
                [
                    'id' => !empty($category['id']) ? $category['id'] : Str::uuid()->toString(),
                ],
                [
                    'category_id' => $category['category_id'] == "" ? null : $category['category_id'],
                    "company_id" => $category['company_id'] == "" ? null : $category['company_id'],
                    'name' =>$category['label'] ?? str_replace(' ', '-', strtolower($category['name'])),
                    'group_by' => $category['group_by'],
                    'label' => $category['name'],
                    'notes' => $category['notes'] == "" ? null : $category['notes'],
                ]
            );
        }
    }
}
