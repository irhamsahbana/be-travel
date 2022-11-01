<?php

namespace App\Http\Repositories\Finder;

use App\Libs\HasAccessControl;
use App\Models\Person;

abstract class AbstractFinder
{
    use HasAccessControl;

    protected $query;
    protected $keyword;
    protected bool $isUsePagination = false;
    protected $orderBy;
    protected string $orderType = 'asc';

    protected bool $isPublic = false;

    private int $page = 1;
    private int $perPage = 10;

    public function setPage(int $page) : void
    {
        $this->page = $page;
    }

    public function isUsePagination() : bool
    {
        return $this->isUsePagination;
    }

    protected function getPage() : int
    {
        return $this->page;
    }

    public function setPerPage(int $perPage) : void
    {
        $this->perPage = $perPage;
    }

    protected function getPerPage() : int
    {
        return $this->perPage;
    }

    public function setOrderBy(string $orderBy) : void
    {
        $this->orderBy = $orderBy;
    }

    public function setOrderType($orderType) : void
    {
        $orderType = strtolower($orderType);

        if ($orderType == 'asc' || $orderType == 'desc')
            $this->orderType = $orderType;
    }

    public function usePagination(bool $isUsePagination) : void
    {
        $this->isUsePagination = $isUsePagination;
    }

    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    public function get() : ?object
    {
        $this->doQuery();

        if ($this->isUsePagination)
            $query = $this->query->paginate($this->getPerPage())->withQueryString();
        else
            $query = $this->query->get();

        return $query;
    }

    abstract protected function doQuery();
}
