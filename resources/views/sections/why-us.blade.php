@php
  $anchor = $data['anchor'] ?? 'why';
  $floor = $data['floor'] ?? '5';
  $floorTitle = $data['floor_title'] ?? '05 Why Choose Us';
  $title = $data['title'] ?? 'Why Building Owners';
  $highlight = $data['title_highlight'] ?? 'Choose Us';
  $subtitle = $data['subtitle'] ?? 'Six reasons our customers trust us with their lifts — year after year.';
  $cards = $data['cards'] ?? [];
@endphp
<section id="{{ $anchor }}" class="sec sec--navy why" data-floor="{{ $floor }}" data-name="Why Us">
  <div class="container">
    <div class="sec-head" data-reveal="up">
      <span class="floor-tag">{!! $floorTitle !!}</span>
      <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
      <p class="sec-desc">@text($subtitle)</p>
    </div>
    <div class="why-grid">
      @foreach($cards as $i => $card)
        <div class="why-card" data-reveal="up" @if($i % 3 !== 0) style="--d: {{ ($i % 3) * 0.1 }}s" @endif>
          <span class="why-card__no">{{ $card['num'] ?? sprintf('%02d', $i + 1) }}</span>
          <div class="why-card__ico"><i class="{{ $card['icon'] ?? 'fas fa-star' }}"></i></div>
          <h3>@text($card['title'] ?? '')</h3>
          <p>@text($card['description'] ?? '')</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
