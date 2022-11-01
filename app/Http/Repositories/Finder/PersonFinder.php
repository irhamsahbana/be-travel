<?php

namespace App\Http\Repositories\Finder;

use App\Models\Person as Model;
use App\Models\Category;

class PersonFinder extends AbstractFinder
{
    private $category;

    public function __construct(array $select = ['id', 'category_id', 'ref_no', 'name', 'email'])
    {
        $this->query = Model::select($select);
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    private function whereCategory()
    {
        if(!empty($this->category)) {
            $category = Category::where('name', $this->category)->first();

            $this->query->where('people.category_id', $category->id ?? null);
        }
    }

    public function whereKeyword()
    {
        if(!empty($this->keyword)) {
            $list = explode(' ', $this->keyword);
            $list = array_map('trim', $list);

            $this->query->where(function($query) use ($list) {
                foreach($list as $x) {
                    $pattern = '%' . $x . '%';
                    $query->orwhere('people.id', 'like', $pattern);
                    $query->orWhere('people.ref_no', 'like', $pattern);
                    $query->orWhere('people.name', 'like', $pattern);
                }
            });
        }
    }

    public function doQuery()
    {
        if ($this->category == 'tutor' && !$this->isPublic)
            $this->filterByAccessControl('tutor-read');

        $this->whereCategory();
        $this->whereKeyword();
    }
}
