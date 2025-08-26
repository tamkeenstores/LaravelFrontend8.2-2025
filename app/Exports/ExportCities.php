<?php

namespace App\Exports;

use App\Models\States;
use App\Models\Region;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportCities implements FromCollection, WithHeadings
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
        $body = [];
        foreach ($this->r as $key => $r) {
            // print_r($key);die();
            if($r == 1 && $key != 'region')   
            $body[] = $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
            // if($r == 1 && $key == 'sub_tags')   
            // $body[] = DB::raw('group_concat(childs.name) as subtag');
        }
        
        $selects = [];
        if($this->r['region'])
        $selects['region'] = Region::selectRaw(DB::raw('group_concat(name) as region'))
            ->whereColumn('states.region', 'region.id');
        
        $reg = isset($this->r['region']) && $this->r['region'] == 1 ? true : false;
        return States::select($body)
        ->addSelect($selects)
        ->when($reg, function ($q) {
            return $q->with('region');
        })
        ->get();
    }
    
    public function headings(): array
    {
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1)   
            $body[] = $key;
        }
        
        return $body;
    }
}
