<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactUs;
use App\Traits\CrudTrait;

class ContactUsApiController extends Controller
{
    use CrudTrait;
    protected $viewVariable = 'contact_us';
    protected $relationKey = 'contact_us_id';


    public function model() {
        $data = ['limit' => -1, 'model' => ContactUs::class, 'sort' => ['id','asc']];
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
        return [];
    }

    public function arrayData(){
        return [];
        // data in coulumn is 0, data in json is 1
    }

    public function models()
    {
        return [];
    }
}
