<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Module;
use App\Traits\CrudTrait;

class RoleApiController extends Controller
{
     use CrudTrait;
    protected $viewVariable = 'role';
    protected $relationKey = 'role_id';


    public function model() {
        $data = ['limit' => -1, 'model' => Role::class, 'sort' => ['id','desc']];
        return $data;
    }
    public function validationRules($resource_id = 0)
    {
        return [];
    }

    public function files(){
        return [];
    }

    public function relations(){
        // , 'users_id' => 'users'
        return ['perm' => 'permission:id,role_id,module_id,view,create,edit,delete'];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return ['modules' => Module::orderBy('id','DESC')->get()];
    }
}
