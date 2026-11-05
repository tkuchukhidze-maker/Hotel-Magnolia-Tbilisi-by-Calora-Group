<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\View\View;

/** Press / news articles, in English (/news/...) and Georgian (/ka/news/...). */
class NewsController extends Controller
{
    public function cmo(): View
    {
        return $this->article('en');
    }

    public function cmoKa(): View
    {
        App::setLocale('ka');

        return $this->article('ka');
    }

    private function article(string $locale): View
    {
        return view('news.article', [
            'locale' => $locale,
            'slug' => 'tornike-kuchukhidze-cmo',
            'en' => route('news.cmo'),
            'ka' => route('news.cmo.ka'),
            'image' => 'assets/img/news/tornike-kuchukhidze-cmo.jpg',
            'published' => '2026-11-05',
        ]);
    }
}
