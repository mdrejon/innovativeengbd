@php
  $anchor = $data['anchor'] ?? 'projects';
  $floor = $data['floor'] ?? '4';
  $floorTitle = $data['floor_title'] ?? '04 Our Works';
  $title = $data['title'] ?? 'Lifts We Have';
  $highlight = $data['title_highlight'] ?? 'Installed & Serviced';
  $subtitle = $data['subtitle'] ?? 'A look at buildings, homes and institutions where our team has installed and maintained lifts.';
@endphp
<section id="{{ $anchor }}" class="sec sec--grey" data-floor="{{ $floor }}" data-name="Our Works">
  <div class="container">
    <div class="sec-head" data-reveal="up">
      <span class="floor-tag">{!! $floorTitle !!}</span>
      <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
      <p class="sec-desc">@text($subtitle)</p>
    </div>
    <div class="filters" id="projFilters" role="toolbar" aria-label="Project types"></div>
    <div class="proj-grid" id="projGrid"></div>
  </div>
</section>
