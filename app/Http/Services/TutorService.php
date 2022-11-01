<?php

namespace App\Http\Services;

use App\Http\Repositories\TutorRepositoryContract;

class TutorService
{
    private TutorRepositoryContract $tutorRepo;

    public function __construct(TutorRepositoryContract $tutorRepo)
    {
        $this->tutorRepo = $tutorRepo;
    }

    public function upsert(object $data)
    {
        return $this->tutorRepo->upsert($this->prepareData($data));
    }

    public function find(string $id)
    {
        return $this->tutorRepo->find($id);
    }

    private function prepareData(object $data) : object
    {
        $socialMedias = implode(',', $data->social_medias);
        $entity = [
            'id' => $data->id,
            'name' => $data->name,
            'city_id' => $data->city_id,
            'address' => $data->address,
            'phone' => $data->phone,
            'email' => $data->email,
            'bio' => $data->bio,
            'social_medias' => $socialMedias,
            'course_ids' => $data->course_ids,
            'course_level_ids' => $data->course_level_ids,
            'schedules' => $data->schedules,
            'fee' => $data->fee,
        ];

        return (object) $entity;
    }
}
