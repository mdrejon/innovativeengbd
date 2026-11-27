@php
  $yielded = fn (string $name) => trim(html_entity_decode($__env->yieldContent($name), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
  $siteName = Cms::setting('site_name', 'Innovative Engineering | Lift & Escalator Solutions');
  $metaTitle = $yielded('meta_title') ?: $siteName;
  $metaDescription = $yielded('meta_description') ?: Cms::setting('site_tagline', 'Total solution for lifts and escalators — installation, repair, maintenance and spare parts in Bangladesh.');
  $metaKeywords = $yielded('meta_keywords');
  $ogImage = $yielded('og_image') ?: 'assets/images/lift/hero-glass-lift.jpg';
  $preloadImage = $yielded('preload_image');
  $version = fn (string $path) => asset($path).'?v='.(@filemtime(public_path($path)) ?: '1');
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $metaTitle }}</title>
    @if($metaDescription)
      <meta content="{{ $metaDescription }}" name="description">
    @endif
    @if($metaKeywords)
      <meta content="{{ $metaKeywords }}" name="keywords">
    @endif
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#123462">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ Cms::asset($ogImage) }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="{{ Cms::asset(Cms::setting('site_favicon', 'assets/images/logo/logo.png')) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ $version('assets/css/style.css') }}">
    <script>document.documentElement.classList.add('js');</script>

    @stack('schema')

    @if($analyticsId = Cms::setting('google_analytics_id'))
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analyticsId }}"></script>
      <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $analyticsId }}');</script>
    @endif
    {!! Cms::setting('header_scripts', '') !!}
  </head>
  <body>
    <!-- LOADER — lift arrives at ground floor, doors open -->
    <div class="loader" id="loader" aria-hidden="true">
      <div class="loader__door loader__door--l"></div>
      <div class="loader__door loader__door--r"></div>
      <div class="loader__panel">
        <img src="{{ Cms::asset(Cms::setting('site_logo', 'assets/images/logo/logo.png')) }}" alt="{{ Cms::setting('brand_name', 'Innovative Engineering') }}">
        <div class="loader__display"><i class="fas fa-caret-down"></i><span id="loaderFloor">5</span></div>
      </div>
    </div>

    @include('partials.header')

    <main id="main">
      @yield('content')
    </main>

    @include('partials.footer')

    <!-- Side floor navigator (desktop) -->
    <nav class="floors" id="floors" aria-label="Section navigator">
      <div class="floors__display" id="floorsDisplay">G</div>
      <ol></ol>
    </nav>

    <!-- Floating contact (desktop) -->
    <div class="fcta">
      <a class="fcta__wa" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', Cms::setting('whatsapp_number', '8801714216666')) }}" data-link="whatsapp" target="_blank" rel="noopener" data-tip="WhatsApp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      <a class="fcta__ms" href="{{ Cms::setting('messenger_url', 'https://m.me/innovativeengbd') }}" data-link="messenger" target="_blank" rel="noopener" data-tip="Messenger" aria-label="Messenger"><i class="fab fa-facebook-messenger"></i></a>
      <a class="fcta__call" href="tel:{{ Cms::setting('phone', '+8801714216666') }}" data-link="call" data-tip="{{ Cms::setting('phone', '01714-216666') }}" aria-label="Call"><i class="fas fa-phone"></i></a>
      <button class="fcta__top" id="toTop" type="button" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>
    </div>

    <!-- Fixed bottom contact bar (mobile) -->
    <nav class="mbar" aria-label="Quick contact">
      <a class="mbar__wa" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', Cms::setting('whatsapp_number', '8801714216666')) }}" data-link="whatsapp" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
      <a class="mbar__ms" href="{{ Cms::setting('messenger_url', 'https://m.me/innovativeengbd') }}" data-link="messenger" target="_blank" rel="noopener"><i class="fab fa-facebook-messenger"></i> Messenger</a>
      <a class="mbar__call" href="tel:{{ Cms::setting('phone', '+8801714216666') }}" data-link="call"><i class="fas fa-phone"></i> Call</a>
    </nav>

    <!-- Detail modal (services & products) -->
    <dialog class="modal" id="modal" aria-label="Details">
      <button class="modal__x" id="modalX" type="button" aria-label="Close"><i class="fas fa-xmark"></i></button>
      <div id="modalBody" style="display: contents"></div>
    </dialog>

    <!-- Project lightbox -->
    <dialog class="lightbox" id="lightbox" aria-label="Project photo">
      <button class="modal__x" id="lbX" type="button" aria-label="Close"><i class="fas fa-xmark"></i></button>
      <figure>
        <img src="" alt="" />
        <figcaption><strong></strong><span></span></figcaption>
      </figure>
      <button class="lb-nav lb-nav--prev" id="lbPrev" type="button" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
      <button class="lb-nav lb-nav--next" id="lbNext" type="button" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
    </dialog>

    {!! Cms::setting('footer_scripts', '') !!}
    <script src="{{ $version('assets/js/data.js') }}"></script>
    <script src="{{ $version('assets/js/main.js') }}"></script>
  </body>
</html>
