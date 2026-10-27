<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['rooms' => config('hotel.rooms')]);
    }

    public function rooms(): View
    {
        return view('pages.rooms', ['rooms' => config('hotel.rooms')]);
    }

    public function ourHotel(): View
    {
        return view('pages.our-hotel');
    }

    public function explore(): View
    {
        return view('pages.explore');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }
}
