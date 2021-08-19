<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function store(Request $request)
    {
        dd($request);
    }

    public function show($id)
    {
        dd('SHOW file' . $id);
    }
}
