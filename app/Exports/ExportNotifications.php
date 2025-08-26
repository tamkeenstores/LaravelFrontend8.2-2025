<?php

namespace App\Exports;

use App\Models\Notification;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportNotifications implements FromCollection, WithHeadings
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
            if($r == 1 && $key == 'for_app')   
            $body[] = DB::raw('CONVERT(for_app,char) as for_app');
            if($r == 1 && $key == 'for_web')   
            $body[] = DB::raw('CONVERT(for_web,char) as for_web');
        }
        return Notification::select($body)
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
