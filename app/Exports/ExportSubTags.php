<?php

namespace App\Exports;

use App\Models\Tag;
use App\Models\SubTags;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportSubTags implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'tags' && $key != 'status')   
            $body[] = $key == 'sorting' ? 'sort' : $key;
            if($r == 1 && $key == 'status')   
            $body[] = DB::raw('CONVERT(status,char) as status');
        }
        // print_r($body);die();
        // if($this->r['sub_tags'])
        $tags = isset($this->r['filter_tags']) ? $this->r['filter_tags'] : [];
        $tagsdata = Tag::whereIn('id', $tags)->pluck('id')->toArray();
        $sub = isset($this->r['tags']) && $this->r['tags'] == 1 ? true : false;
        $tag = SubTags::select($body)
        
        ->when($sub, function ($q) {
            return $q->addSelect(['tags' => Tag::selectRaw(DB::raw('group_concat(name) as tags'))
            ->whereColumn('sub_tags.tag_id', 'tags.id')
            ]);
        })
        ->when($sub, function ($q) {
            return $q->with('parentData');
        })
        ->when($tagsdata, function ($q) use ($tagsdata) {
            return $q->whereIn('tag_id',$tagsdata);
        })
        ->get();
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
