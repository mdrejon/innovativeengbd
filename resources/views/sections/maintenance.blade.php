@php
  $anchor = $data['anchor'] ?? 'maintenance';
  $floor = $data['floor'] ?? '6';
  $floorTitle = $data['floor_title'] ?? '06 Monthly Servicing';
  $title = $data['title'] ?? 'Already Have a Lift?';
  $highlight = $data['title_highlight'] ?? 'Let Us Take Care of It';
  $subtitle = $data['subtitle'] ?? 'With a monthly servicing contract, our technicians visit on a fixed schedule, inspect every part and fix small issues before they turn into costly breakdowns.';
  $benefits = $data['benefits'] ?? [];
  $planRibbon = $data['plan_ribbon'] ?? 'For any brand of lift';
  $planTitle = $data['plan_title'] ?? 'Monthly Maintenance Contract';
  $planSubtitle = $data['plan_subtitle'] ?? 'For apartments, offices, hospitals, factories and shopping malls.';
  $planFeatures = $data['plan_features'] ?? [];
  $stepsTitle = $data['steps_title'] ?? 'How to start a contract';
  $steps = $data['steps'] ?? [];
  $whatsappNumber = preg_replace('/[^0-9]/', '', Cms::setting('whatsapp_number', '8801714216666'));
  $phone = Cms::setting('phone', '01714-216666');
@endphp
<section id="{{ $anchor }}" class="sec sec--grey" data-floor="{{ $floor }}" data-name="Monthly Service">
  <div class="container">
    <div class="amc-top">
      <div data-reveal="left">
        <span class="floor-tag">{!! $floorTitle !!}</span>
        <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
        <p class="sec-desc">@text($subtitle)</p>
        <ul class="benefits">
          @foreach($benefits as $b)
            <li><i class="{{ $b['icon'] ?? 'fas fa-check' }}"></i>@text($b['text'] ?? '')</li>
          @endforeach
        </ul>
      </div>

      <div class="plan" data-reveal="right">
        <span class="plan__ribbon">@text($planRibbon)</span>
        <h3>@text($planTitle)</h3>
        <p>@text($planSubtitle)</p>
        <ul>
          @foreach($planFeatures as $feat)
            <li><i class="fas fa-check"></i><span>@text($feat)</span></li>
          @endforeach
        </ul>
        <div class="plan__btns">
          <a class="btn btn--wa" href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode('Hello Innovative Engineering! I would like to know the price of a Monthly Maintenance Contract.') }}" target="_blank" rel="noopener">
            <i class="fab fa-whatsapp"></i> Ask Price on WhatsApp
          </a>
          <a class="btn btn--ghost" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">
            <i class="fas fa-phone"></i> Call {{ $phone }}
          </a>
        </div>
      </div>
    </div>

    <div class="chks" data-reveal="up">
      <div class="chks__head">
        <div>
          <h3><i class="fas fa-clipboard-check"></i> What We Inspect Every Month</h3>
          <p>Our technicians check <strong data-chk-total>30+</strong> key points on every visit to keep your lift completely safe.</p>
        </div>
        <button class="btn btn--sm" id="chkToggle" type="button" aria-expanded="false" aria-controls="chkList">
          <span>Show full list</span> <i class="fas fa-chevron-down"></i>
        </button>
      </div>
      <div class="chks__list" id="chkList" hidden></div>
    </div>

    <h3 class="sub-head" data-reveal="up">@text($stepsTitle)</h3>
    <div class="steps">
      <div class="steps__line" aria-hidden="true"></div>
      @foreach($steps as $i => $step)
        <div class="step" style="--i: {{ $i }}">
          <div class="step__ico"><i class="{{ $step['icon'] ?? 'fas fa-check' }}"></i></div>
          <h4>@text($step['title'] ?? '')</h4>
          <p>@text($step['description'] ?? '')</p>
        </div>
      @endforeach
    </div>
  </div>
</section>
