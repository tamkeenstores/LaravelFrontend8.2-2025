<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Role;
use App\Models\shippingAddress;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportUser implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public $r = [];
    
    public function __construct($data)
    {
        //print_r($data);die();
        $this->r = $data;
        // print_r($this->r);die();
    }
    
    public function collection()
    {
        $selects = [];
        if($this->r['id'])
        $selects['id'] = DB::raw("users.id AS id");
        if($this->r['email'])
        $selects['email'] = DB::raw("users.email AS email");
        if($this->r['date_of_birth'])
        $selects['date_of_birth'] = DB::raw("users.date_of_birth AS date_of_birth");
        if($this->r['user_device'])
        $selects['user_device'] = DB::raw("users.user_device AS user_device");
        if($this->r['lang'])
        $selects['lang'] = DB::raw("users.lang AS lang");
        if($this->r['role_id'])
        $selects['role_id'] = DB::raw("roledata.name AS role");
        if($this->r['status'])
        $selects['status'] = DB::raw("CONVERT(users.status,char) AS status");
        if($this->r['blacklist'])
        $selects['blacklist'] = DB::raw("CONVERT(users.blacklist,char) AS blacklist");
        if($this->r['full_name'])
        $selects['full_name'] = DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name");
        if($this->r['role_id'])
        $selects['role_id'] = Role::selectRaw(DB::raw('group_concat(name) as role_id'))->whereColumn('users.role_id', 'role.id');
        if($this->r['address'])
        // $selects['address'] = DB::raw('shipaddress.address as address');
        $selects['address'] = DB::raw('city.name as city');
        
        $sub = isset($this->r['role_id']) && $this->r['role_id'] == 1 ? true : false;
        $tag = User::select($selects)
        // ->addSelect($selects)
        ->when($sub, function ($q) {
            return $q->with('role');
        })
        ->leftJoin('role as roledata', function($join) {
            $join->on('users.role_id', '=', 'roledata.id');
        })
        ->leftJoin('shipping_address as shipaddress', function($join) {
            $join->on('users.id', '=', 'shipaddress.customer_id')
            ->where('shipaddress.make_default', '=', 1);
        })
        ->leftJoin('states as city', function($join) {
            $join->on('shipaddress.state_id', '=', 'city.id');
        })
        ->orderBy('id', 'DESC')
        ->limit(50)->get();
        // else
        // $tag = Tag::with('childs')->select(explode(',', implode(',', $body)))->get();
        return $tag;
    }
    
    public function headings(): array
    {
        // return [
        //     'id',
        //     'name',
        //     'name_arabic',
        //     'sorting',
        //     'sub_tags',
        //     'status'
        // ];
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1)   
            $body[] = $key == 'sorting' ? 'sort' : $key;
        }
        //print_r($body);die();
        return $body;
    }
}
