<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_press_release_in_english_and_georgian(): void
    {
        $this->get(route('news.cmo'))->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Tornike Kuchukhidze Appointed CMO', false)
            ->assertSee('"@type":"NewsArticle"', false)
            ->assertSee('hreflang="ka"', false)
            ->assertSee('assets/img/news/tornike-kuchukhidze-cmo.jpg', false)
            ->assertDontSee('class="mobile-book"', false)
            ->assertSee('tornike-kuchukhidze-cmo-cover-en.jpg', false)->assertSee('class="news-brands"', false)->assertSee('<h3>Chateau Mere</h3>', false);

        $this->get(route('news.cmo.ka'))->assertOk()
            ->assertSee('<html lang="ka">', false)
            ->assertSee('თორნიკე კუჭუხიძე დაინიშნა')
            ->assertSee('Noto+Serif+Georgian', false)
            ->assertDontSee('OnePrice');

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('/news/tornike-kuchukhidze-cmo', false)
            ->assertSee('/ka/news/tornike-kuchukhidze-cmo', false);
        $this->get(route('home'))->assertSee(route('news.cmo'), false);
        $this->assertFileExists(public_path('assets/img/news/tornike-kuchukhidze-cmo.jpg'));
    }
}
