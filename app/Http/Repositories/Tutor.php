<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;

use App\Models\Category;
use App\Models\Meta;
use App\Models\Person;

class Tutor extends AbstractRepository
implements TutorRepositoryContract
{
    private Person $person;
    private Meta $meta;

    public function __construct()
    {
        $this->person = new Person();
        $this->meta = new Meta();
        parent::__construct($this->person);
    }

    public function save()
    {
        $this->filterByAccessControl('tutor-create');
        parent::save();
    }

    public function find($id) : ?array
    {
        $person = Person::with(['category', 'city'])
                        ->where('id', $id)
                        ->first()
                        ->makeHidden(['category_id', 'city_id'])
                        ->toArray() ?? null;

        if ($person) {
            $person['social_medias'] = explode(',', $person['social_medias']);

            $courseIds = Meta::select('value')
                                ->where('table_name', $this->person->getTable())
                                ->where('fk_id', $person['id'])
                                ->where('key', 'course_id')
                                ->pluck('value')
                                ->toArray();
            $person['courses'] = Category::whereIn('id', $courseIds)->get()->toArray();

            $courseLevelIds = Meta::select('value')
                                    ->where('table_name', $this->person->getTable())
                                    ->where('fk_id', $person['id'])
                                    ->where('key', 'course_level_id')
                                    ->pluck('value')
                                    ->toArray();
            $person['course_levels'] = Category::whereIn('id', $courseLevelIds)->get()->toArray();

            $person['schedules'] = Meta::select('value')
                                        ->where('table_name', $this->person->getTable())
                                        ->where('fk_id', $person['id'])
                                        ->where('key', 'schedule')
                                        ->pluck('value')
                                        ->toArray();
        }

        return $person;
    }

    public function upsert(object $data)
    {
        DB::beginTransaction();

        try {
            // person
            $person = $this->person->findOrNew($data->id);
            $person->id = $data->id;
            if (empty($person->category_id))
                $person->category_id = Category::where('group_by', 'people')->where('name', 'tutor')->first()->id;
            $person->city_id = $data->city_id;
            $person->name = $data->name;
            $person->address = $data->address;
            $person->phone = $data->phone;
            $person->email = $data->email;
            $person->bio = $data->bio;
            $person->social_medias = $data->social_medias;
            $person->fee = $data->fee;
            $person->save();

            // course (meta) & course level (meta)
            $this->meta->where('table_name', 'people')
                        ->where('fk_id', $person->id)
                        ->where('key', 'course_id')
                        ->whereNotIn('value', $data->course_ids)
                        ->delete();

            $this->meta->where('table_name', 'people')
                        ->where('fk_id', $person->id)
                        ->where('key', 'course_level_id')
                        ->whereNotIn('value', $data->course_level_ids)
                        ->delete();

            $this->meta->where('table_name', 'people')
                        ->where('fk_id', $person->id)
                        ->where('key', 'schedule')
                        ->delete();

            foreach ($data->course_ids as $courseId) {
                Meta::updateOrCreate(
                    [
                        'table_name' => $this->person->getTable(),
                        'fk_id' => $person->id,
                        'key' => 'course_id',
                        'value' => $courseId,
                    ],
                    [
                        'table_name' => $this->person->getTable(),
                        'fk_id' => $person->id,
                        'key' => 'course_id',
                        'value' => $courseId,
                    ]
                );
            }

            foreach ($data->course_level_ids as $courseLevelId) {
               Meta::updateOrCreate(
                    [
                        'table_name' => $this->person->getTable(),
                        'fk_id' => $person->id,
                        'key' => 'course_level_id',
                        'value' => $courseLevelId,
                    ],
                    [
                        'table_name' => $this->person->getTable(),
                        'fk_id' => $person->id,
                        'key' => 'course_level_id',
                        'value' => $courseLevelId,
                    ]
                );
            }

            foreach ($data->schedules as $schedule) {
                $this->meta->create(
                    [
                        'table_name' => $this->person->getTable(),
                        'fk_id' => $person->id,
                        'key' => 'schedule',
                        'value' => $schedule,
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $this->find($person->id);
    }

    public function delete($permanent = null)
    {
        $this->filterByAccessControl('tutor-delete');
        parent::delete();
    }

    public function get()
    {
        $this->filterByAccessControl('tutor-read');
        return $this->model;
    }

    protected function generateData()
    {
        parent::generateData();

        $this->model->category_id = Category::where('name', 'tutor')->first()->id;
    }

    protected function getPrefix()
    {
        return 'lecturer/';
    }
}
