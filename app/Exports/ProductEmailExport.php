<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductEmailExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            return [
                'SKU' => $product->sku,
                'Quantity' => $product->quantity,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Quantity',
        ];
    }
}
