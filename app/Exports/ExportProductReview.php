<?php

namespace App\Exports;

use App\Models\ProductReview;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportProductReview implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'product_id')   
            $body[] = $key;
        }
        
        $selects = [];
        if($this->r['user_id'])
        $selects['user_id'] = User::selectRaw(DB::raw("group_concat(first_name,' ',last_name) AS user_id"))
        ->whereColumn('product_review.user_id', 'users.id');
        
        $sub = isset($this->r['user_id']) && $this->r['user_id'] == 1 ? true : false;
        $review = ProductReview::select(explode(',', implode(',', $body)))
        ->addSelect($selects)
        ->when($sub, function ($q) {
            return $q->with('UserData');
        })
        ->get();
        return $review;
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
