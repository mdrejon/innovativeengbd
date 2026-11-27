@php
  $anchor = $data['anchor'] ?? 'about';
  $floor = $data['floor'] ?? '1';
  $floorTitle = $data['floor_title'] ?? '01 About Us';
  $title = $data['title'] ?? 'Your Trusted Partner for';
  $highlight = $data['title_highlight'] ?? 'Lifts & Escalators';
  $description = $data['description'] ?? '';
  $mainImage = $data['main_image'] ?? 'assets/images/lift/lift-interior-classic.jpg';
  $subImage = $data['sub_image'] ?? 'assets/images/lift/lift-call-button.jpg';
  $expCount = $data['exp_count'] ?? '15';
  $expSuffix = $data['exp_suffix'] ?? '+';
  $expLabel = $data['exp_label'] ?? 'Years of excellence';
  $points = $data['points'] ?? [];
  $buttonLabel = $data['button_label'] ?? 'Our Services';
  $buttonUrl = $data['button_url'] ?? '#services';
  $callLabel = $data['call_label'] ?? 'Call us any time';
  $callNumber = $data['call_number'] ?? '01714-216666';
@endphp
<section id="{{ $anchor }}" class="sec" data-floor="{{ $floor }}" data-name="About Us">
  <div class="container about-grid">
    <div class="about-media" data-reveal="left">
      <div class="about-media__main door-reveal">
        <img src="{{ Cms::asset($mainImage) }}" alt="{{ $title }}" loading="lazy" />
      </div>
      <div class="about-media__sub door-reveal" style="--d: 0.25s">
        <img src="{{ Cms::asset($subImage) }}" alt="Control panel" loading="lazy" />
      </div>
      <div class="about-badge">
        <strong data-count="{{ $expCount }}" data-suffix="{{ $expSuffix }}">{{ $expCount }}{{ $expSuffix }}</strong>
        <span>{!! nl2br(e($expLabel)) !!}</span>
      </div>
    </div>

    <div data-reveal="right">
      <span class="floor-tag">{!! $floorTitle !!}</span>
      <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
      <p class="sec-desc">@text($description)</p>
      <ul class="about-list">
        @foreach($points as $pt)
          <li>
            <i class="{{ $pt['icon'] ?? 'fas fa-check' }}"></i>
            <div>
              <h4>@text($pt['title'] ?? '')</h4>
              <p>@text($pt['description'] ?? '')</p>
            </div>
          </li>
        @endforeach
      </ul>
      <div class="about-sign">
        <a href="{{ Cms::url($buttonUrl) }}" class="btn btn--navy">@text($buttonLabel) <i class="fas fa-arrow-right"></i></a>
        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $callNumber) }}" class="about-call" data-link="call">
          <i class="fas fa-phone"></i>
          <span><small>@text($callLabel)</small><strong>@text($callNumber)</strong></span>
        </a>
      </div>
    </div>
  </div>
</section>
