<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

use App\Libs\AccessControl;
use Illuminate\Contracts\Auth\Authenticatable;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getAccessControl()
    {
        $user = Auth::user();

        if(!empty($user))
            return new AccessControl($user);

        return null;
    }

    public function filterByAccessControl($access, $message = null)
    {
        $accessControl = $this->getAccessControl();

        if(empty($message))
            $message = "Anda tidak punya akses untuk aksi ini [$access].";

        if ($accessControl)
            if(!$accessControl->hasAccess($access))
                AccessControl::throwUnauthorizedException($message);
    }

    public function getUser(): Authenticatable
    {
        return auth()->user()->load([
            'person' => fn ($query) => $query->select('id', 'company_id', 'branch_id', 'category_id'),
            'person.category' => fn ($query) => $query->select('id', 'name')
        ]);
    }
}
