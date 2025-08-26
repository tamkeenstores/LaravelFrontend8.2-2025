<?php

namespace App\Exports;

use App\Models\MarketplaceSales;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportMarketSales implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public $r = [];
    
    public function __construct($data) {
        $this->r = $data;
    }
    
    public function collection()
    {
        $fromdate = $this->r['from'];
        $todate = $this->r['to'];
        
        $selects = [];
        $selects[] = 'marketplacesales.date';
        $selects[] = DB::raw('CONVERT(marketplacesales.amazon,char) as amazon');
        $selects[] = DB::raw('CONVERT(marketplacesales.carefour,char) as carefour');
        $selects[] = DB::raw('CONVERT(marketplacesales.homzmart,char) as homzmart');
        $selects[] = DB::raw('CONVERT(marketplacesales.noon,char) as noon');
        $selects[] = DB::raw('CONVERT(marketplacesales.centerpoint,char) as centerpoint');
        $selects[] = 'marketplacesales.notes';
        $selects[] = DB::raw("CONCAT(users.first_name,' ',users.last_name) AS full_name");
        
        $markets = MarketplaceSales::select($selects)
        ->leftJoin('users', function($join) {
            $join->on('marketplacesales.userid', '=', 'users.id');
        })
        ->when($fromdate != $todate, function ($q) use ($fromdate,$todate) {
            return $q->whereBetween('marketplacesales.created_at', [$fromdate. ' 00:00:00', $todate. ' 23:59:00']);
        })
        ->when($fromdate == $todate, function ($q) use ($fromdate,$todate) {
            return $q->whereDate('marketplacesales.created_at', $fromdate);
        })
        ->orderBy('marketplacesales.id', 'DESC')
        ->groupBy('marketplacesales.id')
        ->get();
        
        return $markets;
    }
    
    
    public function headings(): array
    {
        return [
            'date',
            'amazon',
            'carefour',
            'homzmart',
            'noon',
            'centerpoint',
            'notes',
            'user'
        ];
    }
}