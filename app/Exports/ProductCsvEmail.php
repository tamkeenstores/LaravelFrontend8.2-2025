<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductCsvEmail implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
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
                'Price' => $product->price,
                'Sale Price' => $product->sale_price,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Quantity',
            'Price',
            'Sale Price',
        ];
    }
}
