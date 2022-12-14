<?php

namespace App\Http\Repositories\Finder;

use App\Models\Category as Model;
use Illuminate\Support\Str;

class CategoryFinder extends AbstractFinder
{
    protected array $groups = [];
    protected ?string $companyId;

    public function __construct()
    {
        $this->query = Model::select('id', 'name', 'group_by', 'label', 'notes');
    }

    public function setGroup(string $groups)
    {
        $this->groups = explode(",", $groups);
    }

    public function setParentId(?string $parentId)
    {
        if(!empty($parentId)) {
            $this->query->where('categories.category_id', $parentId);
        } else {
            $this->query->whereNull('categories.category_id');
        }
    }

    public function setCompany(?string $companyId)
    {
        $this->companyId = $companyId;
    }

    public function whereKeyword()
    {
        if(!empty($this->keyword)) {
            $list = explode(' ', $this->keyword);
            $list = array_map('trim', $list);

            $this->query->where(function($query) use ($list) {
                foreach($list as $x) {
                    $pattern = '%' . $x . '%';
                    $query->orWhere('categories.id', 'like', $pattern);
                    $query->orWhere('categories.label', 'like', $pattern);
                }
            });
        }
    }

    private function whereOrderBy()
    {
        switch ($this->orderBy) {
            case 'name':
                $this->query->orderBy('name', $this->orderType);
                break;
            case 'label':
                $this->query->orderBy('label', $this->orderType);
                break;
            case 'group_by':
                $this->query->orderBy('group_by', $this->orderType);
                break;
            case 'notes':
                $this->query->orderBy('notes', $this->orderType);
        }
    }

    private function whereGroups()
    {
        $this->query->whereIn('group_by', $this->groups);
    }

    private function whereCompany()
    {
        if(!empty($this->companyId))
            $this->query->where('company_id', $this->companyId);
    }

    protected function doQuery()
    {
        // map group to kebab case
        $groups = array_map(function($group) {
            return str_replace(['_'], '-', $group);
        }, $this->groups);

        foreach ($groups as $group) {
            if ($group === 'permission_groups')
                $this->filterByAccessControl('access-right-read');
            else
                $this->filterByAccessControl(sprintf('category-%s-read', $group), sprintf('Tidak memiliki hak akses untuk melihat data %s', $group));
        }

        $this->whereGroups();
        $this->whereKeyword();
        $this->whereCompany();
        $this->whereOrderBy();
    }
}
