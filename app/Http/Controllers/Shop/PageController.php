<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('shop.pages.about');
    }

    public function contact(): View
    {
        return view('shop.pages.contact');
    }

    public function terms(): View
    {
        return view('shop.pages.terms');
    }

    public function privacy(): View
    {
        return view('shop.pages.privacy');
    }
}
