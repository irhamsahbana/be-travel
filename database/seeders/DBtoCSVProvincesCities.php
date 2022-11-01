<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\{
    Category,
};

class DBtoCSVProvincesCities extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csvFilePath = storage::disk('database')->path('csv/provinces-cities.csv');
        $csv = new \ParseCsv\Csv();
        if (!file_exists($csvFilePath)) {
           touch($csvFilePath);
            $csv->save(
                $csvFilePath,
                [['ID', 'CATEGORY_ID', 'NAME', 'LABEL', 'NOTES', 'GROUP_BY']]
            );
        }

        $data = Category::whereIn('group_by', ['provinces', 'cities'])
                        ->orderBy('group_by', 'desc')
                        ->orderBy('notes', 'asc')
                        ->get()
                        ->toArray();

        $toCSV = [];

        foreach($data as $row) {
            $toCSV[] = [
                $row['id'],
                $row['category_id'] === null ? "" : $row['category_id'],
                $row['name'],
                $row['label'],
                $row['notes'] === null ? "" : $row['notes'],
                $row['group_by'],
            ];
        }

        $csv->save($csvFilePath, $toCSV, true);
    }
}
