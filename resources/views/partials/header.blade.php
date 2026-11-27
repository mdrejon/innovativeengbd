@php
  $header = Cms::layout('layout_header');
  $menu = Cms::layout('menu_primary');
  $phone = $header['phone'] ?? Cms::setting('phone', '01714-216666');
  $email = $header['email'] ?? Cms::setting('sales_email', 'palash_pe@yahoo.com');
  $location = $header['location_notice'] ?? 'Agrabad, Chattogram · Malibag, Dhaka';
  $emergencyBadge = $header['emergency_badge'] ?? '24/7 Emergency Service';
  $ctaLabel = $header['cta_label'] ?? 'Get a Quote';
  $ctaUrl = $header['cta_url'] ?? '#contact';
@endphp
<header class="hdr" id="hdr">
  <div class="topbar">
    <div class="container">
      <div class="topbar__info">
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" data-link="call"><i class="fas fa-phone"></i>@text($phone)</a>
        <a href="mailto:{{ $email }}" data-link="email"><i class="fas fa-envelope"></i>@text($email)</a>
        <span><i class="fas fa-location-dot"></i>@text($location)</span>
      </div>
      <div class="topbar__right">
        <span class="topbar__live">@text($emergencyBadge)</span>
        <div class="lang" aria-label="Language">
          <a href="/" class="is-active" lang="en">EN</a>
          <a href="/index-bn.html" lang="bn">বাংলা</a>
        </div>
      </div>
    </div>
  </div>

  <div class="nav">
    <div class="container">
      <a href="{{ url('/') }}#home" class="brand" aria-label="{{ Cms::setting('brand_name', 'Innovative Engineering') }} — home">
        <img src="{{ Cms::asset(Cms::setting('site_logo', 'assets/images/logo/logo.png')) }}" alt="{{ Cms::setting('brand_name', 'Innovative Engineering') }}" width="52" height="52" />
        <span class="brand__txt"><strong>{{ Cms::setting('brand_name', 'Innovative Engineering') }}</strong><small>Lift &amp; Escalator Solutions</small></span>
      </a>

      <nav aria-label="Main menu">
        <ul class="menu">
          @foreach($menu as $item)
            <li><a href="{{ Cms::url($item['url'] ?? '#') }}">{{ $item['label'] ?? '' }}</a></li>
          @endforeach
        </ul>
      </nav>

      <div class="nav__cta">
        <a href="{{ Cms::url($ctaUrl) }}" class="btn btn--sm"><i class="fas fa-paper-plane"></i> @text($ctaLabel)</a>
        <a href="/index-bn.html" class="lang-m" lang="bn" aria-label="বাংলায় দেখুন">বাংলা</a>
        <button class="ham" id="ham" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mMenu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>
  <div class="progress" aria-hidden="true"></div>
</header>

<!-- Mobile menu (opened by hamburger) -->
<nav class="m-menu" id="mMenu" aria-label="Mobile menu">
  <ul>
    @foreach($menu as $i => $item)
      <li><a href="{{ Cms::url($item['url'] ?? '#') }}" style="--i: {{ $i }}"><i class="{{ $item['icon'] ?? 'fas fa-chevron-right' }}"></i>@text($item['label'] ?? '')</a></li>
    @endforeach
  </ul>
  <div class="m-menu__foot">
    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn btn--navy btn--block" data-link="call"><i class="fas fa-phone"></i> Call @text($phone)</a>
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', Cms::setting('whatsapp_number', '8801714216666')) }}" class="btn btn--wa btn--block" data-link="whatsapp" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp Us</a>
    <div class="lang">
      <a href="/" class="is-active" lang="en">EN</a>
      <a href="/index-bn.html" lang="bn">বাংলা</a>
    </div>
  </div>
</nav>
