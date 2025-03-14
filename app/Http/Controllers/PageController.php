<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('about');
    }

    public function communities()
    {
        return view('communities');
    }

    public function contracts()
    {
        return view('contracts');
    }

    public function events()
    {
        return view('events');
    }

    public function medical()
    {
        return view('medical');
    }

    public function services()
    {
        return view('services');
    }
}
