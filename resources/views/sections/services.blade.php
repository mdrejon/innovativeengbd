@php
  $anchor = $data['anchor'] ?? 'services';
  $floor = $data['floor'] ?? '2';
  $floorTitle = $data['floor_title'] ?? '02 Our Services';
  $title = $data['title'] ?? 'Everything Your Lift Needs,';
  $highlight = $data['title_highlight'] ?? 'Under One Roof';
  $subtitle = $data['subtitle'] ?? 'From a brand-new installation to monthly care and emergency repair — we handle it all.';
  $items = $data['items'] ?? [];
@endphp
<section id="{{ $anchor }}" class="sec sec--grey" data-floor="{{ $floor }}" data-name="Services">
  <div class="container">
    <div class="sec-head" data-reveal="up">
      <span class="floor-tag">{!! $floorTitle !!}</span>
      <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
      <p class="sec-desc">@text($subtitle)</p>
    </div>

    <div class="svc-grid">
      @foreach($items as $i => $item)
        <article class="svc" data-reveal="up" @if($i % 3 !== 0) style="--d: {{ ($i % 3) * 0.1 }}s" @endif>
          <div class="svc__img door-reveal" @if($i % 3 !== 0) style="--d: {{ ($i % 3) * 0.1 }}s" @endif>
            <span class="svc__num">{{ $item['num'] ?? sprintf('%02d', $i + 1) }}</span>
            <img src="{{ Cms::asset($item['image'] ?? 'assets/images/lift/lobby-modern.jpg') }}" alt="{{ $item['title'] ?? '' }}" loading="lazy" />
          </div>
          <div class="svc__body">
            <div class="svc__ico"><i class="{{ $item['icon'] ?? 'fas fa-elevator' }}"></i></div>
            <h3>@text($item['title'] ?? '')</h3>
            <p>@text($item['description'] ?? '')</p>
            <button class="link-arrow" type="button" data-service="{{ $item['service_key'] ?? 'install' }}">Learn more <i class="fas fa-arrow-right"></i></button>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>
