@php
  $footer = Cms::layout('layout_footer');
  $menuFooter = Cms::layout('menu_footer');
  $social = Cms::layout('social_links');
  $phone = Cms::setting('phone', '01714-216666');
  $phone2 = Cms::setting('phone2', '01829-984722');
  $email = Cms::setting('sales_email', 'palash_pe@yahoo.com');
  $intro = $footer['intro'] ?? 'Installation, repair, monthly maintenance and spare parts for lifts and escalators across Bangladesh.';
  $headOffice = $footer['head_office'] ?? Cms::setting('head_office', 'Barik Building, 10 Sk. Mujib Road (2nd Fl.), Agrabad C/A, Chattogram');
  $branchOffice = $footer['branch_office'] ?? Cms::setting('branch_office', '63/1 VIP Road, Malibag, Dhaka-1217');
  $copyright = $footer['copyright'] ?? '© '.date('Y').' Innovative Engineering. All rights reserved.';
  $tagline = $footer['tagline'] ?? 'Total Solution for Lifts & Escalators';
@endphp
<footer class="ftr">
  <div class="container ftr__top">
    <div>
      <a href="{{ url('/') }}#home" class="brand">
        <img src="{{ Cms::asset(Cms::setting('site_logo', 'assets/images/logo/logo.png')) }}" alt="{{ Cms::setting('brand_name', 'Innovative Engineering') }}" width="52" height="52" loading="lazy" />
        <span class="brand__txt"><strong>{{ Cms::setting('brand_name', 'Innovative Engineering') }}</strong><small>Lift &amp; Escalator Solutions</small></span>
      </a>
      <p>@text($intro)</p>
      @include('partials.social-links', ['links' => $social])
    </div>
    <div>
      <h4>Quick Links</h4>
      <ul class="ftr__links">
        @foreach($menuFooter as $item)
          <li><a href="{{ Cms::url($item['url'] ?? '#') }}">@text($item['label'] ?? '')</a></li>
        @endforeach
      </ul>
    </div>
    <div>
      <h4>Our Services</h4>
      <ul class="ftr__links">
        <li><a href="#services">New Lift Installation</a></li>
        <li><a href="#services">Escalator Installation</a></li>
        <li><a href="#services">Lift Repair & Servicing</a></li>
        <li><a href="#maintenance">Monthly Maintenance</a></li>
        <li><a href="#products">Spare Parts Supply</a></li>
      </ul>
    </div>
    <div>
      <h4>Get in Touch</h4>
      <ul class="ftr__ci">
        <li><i class="fas fa-location-dot"></i><span>@text($headOffice)</span></li>
        <li><i class="fas fa-building"></i><span>@text($branchOffice)</span></li>
        <li>
          <i class="fas fa-phone"></i>
          <span>
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" data-link="call">@text($phone)</a>, 
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone2) }}" data-link="call2">@text($phone2)</a>
          </span>
        </li>
        <li><i class="fas fa-envelope"></i><a href="mailto:{{ $email }}" data-link="email">@text($email)</a></li>
      </ul>
    </div>
  </div>
  <div class="container ftr__bot">
    <span>@text($copyright)</span>
    <span>@text($tagline)</span>
  </div>
</footer>
