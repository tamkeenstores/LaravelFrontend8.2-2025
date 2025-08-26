<?php

namespace App\Exports;

use App\Models\RegionalModule;
use App\Models\States;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportRegional implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public $r = [];
    
    public function __construct($data)
    {
        $this->r = $data;
    }
    
    public function collection()
    {
        $body = [];
        foreach ($this->r as $key => $r) {
            if($r == 1 && $key != 'cities')   
            $body[] = 'regional_modules.'.$key;
            if($r == 1 && $key == 'cities')
            $body[] = DB::raw('group_concat(pro.name) as citydata');
            
        }
        $city = isset($this->r['cities']) && $this->r['cities'] == 1 ? true : false;
        $regional = RegionalModule::select($body)
        // ->addSelect(['cities' => States::selectRaw(DB::raw('group_concat(name) as '))
        //     ->whereColumn('region.id', 'states.region')
        // ])
        ->leftJoin('regional_city as regionalcity', function($join) {
            $join->on('regional_modules.id', '=', 'regionalcity.regional_id');
        })
        ->leftJoin('states as pro', function($join) {
            $join->on('regionalcity.city_id', '=', 'pro.id');
        })
        ->when($city, function ($q) {
            return $q->with('citydata');
        })
        ->groupBy('regional_modules.id')
        ->get();
        
        // print_r($regional);die();
        
        return $regional;
        
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
