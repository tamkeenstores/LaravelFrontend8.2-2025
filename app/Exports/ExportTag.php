<?php

namespace App\Exports;
use App\Models\Tag;
use App\Models\SubTags;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportTag implements FromCollection, WithHeadings
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
            if($r == 1 && $key != 'sub_tags')   
            $body[] = $key == 'sorting' ? 'sort' : $key;
            // if($r == 1 && $key == 'status')
            // $body[] = $key['status'] == 1 ? 'enable' : 'disable';
            // if($r == 1 && $key == 'sub_tags')   
            // $body[] = DB::raw('group_concat(childs.name) as subtag');
        }
        //print_r($body);die();
        // if($this->r['sub_tags'])
        $selects = [];
        if($this->r['sub_tags'])
        $selects['subtags'] =  SubTags::selectRaw(DB::raw('group_concat(name) as subtags'))->whereColumn('tags.id', 'sub_tags.tag_id');
        
        $sub = isset($this->r['sub_tags']) && $this->r['sub_tags'] == 1 ? true : false;
        $tag = Tag::select(explode(',', implode(',', $body)))
        ->addSelect($selects)
        ->when($sub, function ($q) {
            return $q->with('childs');
        })
        ->get();
        // print_r($tag->status == 1 ? 'enable' : 'disable');die();
        
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
