<?php

namespace App\Libs;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AbstractAccessControl
{
    protected $model;

    public function __construct(Authenticatable $model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function hasAccessOrThrow($access, $message = null)
    {
        if(!$this->hasAccess($access)) {
            self::throwUnauthorizedException($message);
        }
    }

    /*
     * Throw exception template
     */
    public static function throwUnauthorizedException($message = null)
    {
        abort(403, $message);
    }
}
