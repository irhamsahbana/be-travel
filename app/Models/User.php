<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

use App\Libs\HasAccessControl;
use App\Libs\AccessControl;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasAccessControl, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'branch_id',
        'person_id',
        'permission_group_id',
        'name',
        'email',
        'username',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function hasAccess(string $access) : bool
    {
        $this->setAccessControl($this->getAccessControl());
        $accessControl = $this->getUserAccessControl();

        return $accessControl->hasAccess($access);
    }

    public function getUserPermissions() : Collection
    {
        $this->setAccessControl($this->getAccessControl());
        $accessControl = $this->getUserAccessControl();

        return $accessControl->getPermissions();
    }

    public function getUserPermissionGroups() : Collection
    {
        $this->setAccessControl($this->getAccessControl());
        $accessControl = $this->getUserAccessControl();

        return $accessControl->getPermissionGroups();
    }

    private function getAccessControl()
    {
        $user = Auth::user();

        if(!empty($user))
            return new AccessControl($user);

        return null;
    }

    // public function permissionGroup()
    // {
    //     return $this->belongsTo(Category::class, 'permission_group_id');
    // }

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }
}
