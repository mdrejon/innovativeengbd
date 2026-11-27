@php
  $anchor = $data['anchor'] ?? 'home';
  $floor = $data['floor'] ?? 'G';
  $badge = $data['badge'] ?? 'Total Lift & Escalator Solutions';
  $badgeIcon = $data['badge_icon'] ?? 'fas fa-shield-halved';
  $title = $data['title'] ?? 'Safe, Smooth & Reliable';
  $highlight = $data['title_highlight'] ?? 'Lifts and Escalators';
  $rotatorPrefix = $data['rotator_prefix'] ?? 'We';
  $rotatorWords = $data['rotator_words'] ?? ["install new lifts", "repair & service old lifts", "maintain lifts every month", "supply genuine spare parts", "install escalators"];
  $rotatorWordsJson = is_array($rotatorWords) ? json_encode($rotatorWords) : (string) $rotatorWords;
  $rotatorSuffix = $data['rotator_suffix'] ?? 'for homes, offices, hospitals and malls across Bangladesh.';
  $chips = $data['chips'] ?? [];
  $buttons = $data['buttons'] ?? [];
  $slides = $data['slides'] ?? [];
  $stats = $data['stats'] ?? [];
@endphp
<section id="{{ $anchor }}" class="hero" data-floor="{{ $floor }}" data-name="Home">
  <div class="hero__bg" aria-hidden="true">
    @foreach($slides as $i => $slide)
      @if($i === 0)
        <div class="slide is-active" style="background-image: url('{{ Cms::asset($slide['image'] ?? 'assets/images/lift/hero-glass-lift.jpg') }}')"></div>
      @else
        <div class="slide" data-bg="{{ Cms::asset($slide['image'] ?? '') }}"></div>
      @endif
    @endforeach
  </div>
  <div class="hero__grid" aria-hidden="true"></div>

  <div class="hero__main">
    <div class="container">
      <div>
        <div class="hero__badge" data-reveal="up"><i class="{{ $badgeIcon }}"></i> @text($badge)</div>
        <h1 data-reveal="up" style="--d: 0.1s">
          @text($title) <span class="accent">@text($highlight)</span>
        </h1>
        <p class="hero__rotator" data-reveal="up" style="--d: 0.2s">
          @text($rotatorPrefix)
          <span class="rotator" data-words='{!! $rotatorWordsJson !!}'>
            <span>{{ is_array($rotatorWords) ? ($rotatorWords[0] ?? 'install new lifts') : 'install new lifts' }}</span>
          </span>
          @text($rotatorSuffix)
        </p>
        <ul class="hero__chips" data-reveal="up" style="--d: 0.3s">
          @foreach($chips as $chip)
            <li><i class="{{ $chip['icon'] ?? 'fas fa-check' }}"></i>@text($chip['text'] ?? '')</li>
          @endforeach
        </ul>
        <div class="hero__btns" data-reveal="up" style="--d: 0.4s">
          @foreach($buttons as $btn)
            <a href="{{ Cms::url($btn['url'] ?? '#') }}" class="{{ $btn['style'] ?? 'btn' }}" @if(!empty($btn['new_tab'])) target="_blank" rel="noopener" @endif>
              @if(!empty($btn['icon'])) <i class="{{ $btn['icon'] }}"></i> @endif @text($btn['label'] ?? '')
            </a>
          @endforeach
        </div>
      </div>

      <!-- Animated lift shaft -->
      <div class="shaft-wrap" aria-hidden="true">
        <div class="shaft" data-reveal="zoom" style="--d: 0.35s">
          <div class="shaft__display"><i id="liftArrow" class="fas fa-caret-up is-idle"></i><span id="liftFloor">G</span></div>
          <div class="shaft__body">
            <ul class="shaft__floors">
              <li><span>5</span></li>
              <li><span>4</span></li>
              <li><span>3</span></li>
              <li><span>2</span></li>
              <li><span>1</span></li>
              <li><span>G</span></li>
            </ul>
            <div class="shaft__rail shaft__rail--l"></div>
            <div class="shaft__rail shaft__rail--r"></div>
            <div class="shaft__cw"></div>
            <div class="shaft__car" id="liftCar">
              <div class="shaft__cabin">
                <i class="fas fa-user-group"></i>
                <div class="shaft__door shaft__door--l"></div>
                <div class="shaft__door shaft__door--r"></div>
              </div>
            </div>
          </div>
          <div class="shaft__base"></div>
          <p class="shaft__caption">Smooth ride · Precise levelling</p>
        </div>
      </div>
    </div>
  </div>

  <div class="hero__stats">
    <div class="container">
      @foreach($stats as $stat)
        <div class="stat">
          <i class="{{ $stat['icon'] ?? 'fas fa-star' }}"></i>
          <div>
            @if(!empty($stat['count']))
              <strong data-count="{{ $stat['count'] }}" data-suffix="{{ $stat['suffix'] ?? '' }}">{{ $stat['value'] ?? ($stat['count'].($stat['suffix'] ?? '')) }}</strong>
            @else
              <strong>@text($stat['value'] ?? '')</strong>
            @endif
            <span>@text($stat['label'] ?? '')</span>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
