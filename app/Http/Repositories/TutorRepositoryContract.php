<?php

namespace App\Http\Repositories;

use App\Models\Person;

interface TutorRepositoryContract
{
    public function upsert(object $data);
    public function find($id) : ?array;
}
