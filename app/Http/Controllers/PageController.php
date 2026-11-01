<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Support\RoomCatalog;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['rooms' => RoomCatalog::all()]);
    }

    public function rooms(): View
    {
        return view('pages.rooms', ['rooms' => RoomCatalog::all()]);
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
