@php
  $anchor = $data['anchor'] ?? 'products';
  $floor = $data['floor'] ?? '3';
  $floorTitle = $data['floor_title'] ?? '03 Products';
  $title = $data['title'] ?? 'Lifts, Escalators &';
  $highlight = $data['title_highlight'] ?? 'Spare Parts';
  $subtitle = $data['subtitle'] ?? 'Choose a category, then tap any product for details. Ask us for the price — we reply quickly.';
  $note = $data['note'] ?? 'No online payment needed — send us a message and we\'ll share the best price for your building.';
@endphp
<section id="{{ $anchor }}" class="sec" data-floor="{{ $floor }}" data-name="Products">
  <div class="container">
    <div class="sec-head" data-reveal="up">
      <span class="floor-tag">{!! $floorTitle !!}</span>
      <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
      <p class="sec-desc">@text($subtitle)</p>
    </div>
    <div class="filters" id="prodFilters" role="toolbar" aria-label="Product categories"></div>
    <div class="prod-grid" id="prodGrid" aria-live="polite"></div>
    <p class="note" data-reveal="up">
      <i class="fas fa-circle-info"></i> @text($note)
    </p>
  </div>
</section>
