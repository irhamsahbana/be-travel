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
         $this->generics($csvFilePath);
       }
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
}
