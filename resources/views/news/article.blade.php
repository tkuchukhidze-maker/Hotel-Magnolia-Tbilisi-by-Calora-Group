@extends('layouts.app', ['hideMobileBar' => true])

@php
    $ka = $locale === 'ka';
    $t = fn (string $kaText, string $enText) => $ka ? $kaText : $enText;
    $title = $t(
        'Episode Food Holding-ისა და by Calora Group-ის მარკეტინგის ხელმძღვანელად თორნიკე კუჭუხიძე დაინიშნა',
        'Tornike Kuchukhidze Appointed CMO of Episode Food Holding and by Calora Group'
    );
    $dek = $t(
        'კრეატიული პროექტები, ახალი გამოცდილებები, ციფრული ინოვაციები და ამბიციური განვითარების სტრატეგია: ჯგუფი სტუმართმოყვარეობის ბრენდების განვითარების ახალ ეტაპს იწყებს.',
        'Creative projects, new experiences, digital innovation and an ambitious growth strategy: the group begins a new chapter for its hospitality brands.'
    );
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $title,
        'description' => $dek,
        'image' => [\App\Support\PublicSite::url($image)],
        'datePublished' => $published.'T14:00:00+04:00',
        'inLanguage' => $locale,
        'author' => ['@type' => 'Organization', 'name' => 'Calora Group'],
        'publisher' => ['@type' => 'Organization', 'name' => config('hotel.name'), 'url' => \App\Support\PublicSite::url('/')],
        'mainEntityOfPage' => \App\Support\PublicSite::canonical(),
    ];
@endphp

@section('title', $title.' | Hotel Magnolia')
@section('description', $dek)
@section('crumb', $t('სიახლეები', 'News'))
@section('og_image', asset($image))

@push('head')
<link rel="alternate" hreflang="en" href="{{ \App\Support\PublicSite::url(route('news.cmo', [], false)) }}">
<link rel="alternate" hreflang="ka" href="{{ \App\Support\PublicSite::url(route('news.cmo.ka', [], false)) }}">
<link rel="alternate" hreflang="x-default" href="{{ \App\Support\PublicSite::url(route('news.cmo', [], false)) }}">
<meta property="og:type" content="article">
<meta property="article:published_time" content="{{ $published }}T14:00:00+04:00">
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@if ($ka)
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Georgian:wght@400;500;600&family=Noto+Sans+Georgian:wght@400;500;600;700&display=swap" rel="stylesheet">
@endif
<style>
.news{--news-display:{{ $ka ? "'Noto Serif Georgian', Georgia, serif" : 'var(--font-display)' }};--news-text:{{ $ka ? "'Noto Sans Georgian', var(--font-text)" : 'var(--font-text)' }};font-family:var(--news-text)}
.news-head{padding:clamp(48px,7vw,88px) 0 0}
.news-head .wrap{max-width:900px}
.news-meta{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px 24px}
.news-lang{display:inline-flex;border:1px solid var(--line-2);border-radius:999px;overflow:hidden}
.news-lang a,.news-lang span{padding:8px 16px;font-size:14px;font-weight:600;text-decoration:none;min-height:40px;display:inline-flex;align-items:center}
.news-lang span{background:var(--cocoa);color:var(--ivory)}
.news-lang a{color:var(--cocoa)}
.news-lang a:hover{background:var(--sand)}
.news h1{font-family:var(--news-display);font-weight:500;font-size:clamp(34px,5vw,{{ $ka ? '52px' : '68px' }});line-height:{{ $ka ? '1.25' : '1.02' }};margin:22px 0 18px;color:var(--cocoa);text-wrap:balance}
.news-dek{font-size:clamp(18px,2vw,21px);line-height:1.6;color:var(--muted);margin:0;max-width:44em}
.news-date{font-size:14px;color:var(--muted);margin:18px 0 0}
.news-photo{margin:clamp(32px,5vw,56px) auto 0;max-width:1180px}
.news-photo img{width:100%;height:auto;aspect-ratio:16/9;object-fit:cover;display:block;border-radius:var(--radius-lg)}
.news-photo figcaption{font-size:13px;color:var(--muted);margin-top:10px}
.news-body{max-width:44rem;margin:0 auto;padding:clamp(40px,6vw,72px) 0 24px}
.news-body p{font-size:18px;line-height:1.85;color:var(--text);margin:0 0 20px}
.news-body h2{font-family:var(--news-display);font-weight:500;font-size:clamp(26px,3vw,{{ $ka ? '30px' : '40px' }});line-height:1.2;color:var(--cocoa);margin:48px 0 16px;text-wrap:balance}
.news-body ul{margin:0 0 20px;padding-left:1.2em;color:var(--text);font-size:18px;line-height:1.75}
.news-body li{margin:8px 0}
.news-body strong{color:var(--cocoa)}
.news-quote{padding:clamp(56px,8vw,96px) 0}
.news-quote .wrap{max-width:52rem;margin:0 auto;position:relative}
.news-quote .branch{position:absolute;right:-20px;top:-40px;width:220px;height:110px;color:var(--terracotta);opacity:.35}
.news-quote h2{font-family:var(--news-display);font-weight:500;font-size:clamp(28px,3.4vw,{{ $ka ? '34px' : '46px' }});line-height:1.2;color:var(--terracotta);margin:0 0 24px;text-wrap:balance}
.news-quote blockquote{margin:0;font-family:var(--news-display);font-size:{{ $ka ? '19px' : '22px' }};line-height:1.8;color:var(--cocoa)}
.news-quote blockquote p{margin:0 0 16px}
.news-quote cite{display:block;font-style:normal;font-family:var(--news-text);font-size:14px;font-weight:700;letter-spacing:.04em;color:var(--muted);margin-top:20px}
.news-end{max-width:44rem;margin:0 auto;padding:8px 0 clamp(64px,8vw,96px);display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;border-top:1px solid var(--line);padding-top:24px}
.news-end a.li{color:var(--terracotta);font-weight:600}
</style>
@endpush

@section('content')
<article class="news" lang="{{ $locale }}">
  <header class="news-head">
    <div class="container wrap">
      <div class="news-meta">
        <p class="eyebrow">{{ $t('პრეს-რელიზი', 'Press release') }}</p>
        <nav class="news-lang" aria-label="{{ $t('ენა', 'Language') }}">
          @if ($ka)<a href="{{ route('news.cmo') }}" hreflang="en" lang="en">English</a><span aria-current="page">ქართული</span>
          @else<span aria-current="page">English</span><a href="{{ route('news.cmo.ka') }}" hreflang="ka" lang="ka">ქართული</a>@endif
        </nav>
      </div>
      <h1>{{ $title }}</h1>
      <p class="news-dek">{{ $dek }}</p>
      <p class="news-date">{{ $t('თბილისი / ბათუმი | 5 ნოემბერი, 2026', 'Tbilisi / Batumi | 5 November 2026') }}</p>
    </div>
  </header>

  <figure class="news-photo container">
    <img src="{{ asset($image) }}" alt="{{ $t('თორნიკე კუჭუხიძე', 'Tornike Kuchukhidze') }}" width="1600" height="900" fetchpriority="high">
    <figcaption>{{ $t('თორნიკე კუჭუხიძე, CMO, მარკეტინგის დეპარტამენტის ხელმძღვანელი', 'Tornike Kuchukhidze, Chief Marketing Officer') }}</figcaption>
  </figure>

  @include('news.'.$slug.'-'.$locale)
</article>
@endsection
