<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExtraPagesController extends Controller
{
    public function ApplePage() {
        // $destinationPath=public_path()."/media/apple-developer-merchantid-domain-association.txt";
        return view('apple-developer-merchantid-domain-association');
    }
}
