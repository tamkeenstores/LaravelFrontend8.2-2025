<?php

namespace App\Exports;

use App\Models\DoorStepDelivery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportDoorStep implements FromCollection, WithHeadings
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
            if($r == 1)   
            $body[] = $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
        }
        // print_r($body);die();
        // if($body['type'] == 2) {
        //     $body['type'] == Brands;
        // }
        return DoorStepDelivery::select($body)
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
