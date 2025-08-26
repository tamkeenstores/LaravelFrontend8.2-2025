<?php

namespace App\Custom;

use Illuminate\Routing\Router as BaseRouter;
use Illuminate\Support\Str;

class Router extends BaseRouter
{
    public function customResource($name, $controller, array $options = []) {
        $model = Str::singular($name); // this is optional, i need it for Route Model Binding
        $model = str_replace('/','',$model);
        $model = str_replace('-','_',$model);
        $this
            ->get( // set the http methods
                $name .'/{' . $model . '}/delete',
                $controller . '@destroy'
            )->name($name . '.delete');
            
            
        $this
            ->post( // set the http methods
                $name .'/{' . $model . '}/update',
                $controller . '@update'
            )->name($name . '.customupdate');
            
        $this
            ->post( // set the http methods
                $name .'/multidelete',
                $controller . '@multidelete'
        )->name($name . '.multidelete');   

        return $this->resource($name, $controller, $options);
    }
}