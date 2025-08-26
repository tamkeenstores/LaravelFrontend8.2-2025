<?php

namespace App\Exports;

use App\Models\States;
use App\Models\Region;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportRegion implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'cities')   
            $body[] = $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
            // if($r == 1 && $key == 'sub_tags')   
            // $body[] = DB::raw('group_concat(childs.name) as subtag');
        }
        
        $selects = [];
        if($this->r['cities'])
        $selects['cities'] = States::selectRaw(DB::raw('group_concat(name) as cities'))
            ->whereColumn('region.id', 'states.region');
        
        $city = isset($this->r['cities']) && $this->r['cities'] == 1 ? true : false;
        return Region::select($body)
        ->addSelect($selects)
        ->when($city, function ($q) {
            return $q->with('cityname');
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
