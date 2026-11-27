@php
  $title = $data['title'] ?? ($page->title ?? '');
  $highlight = $data['title_highlight'] ?? '';
  $eyebrow = $data['eyebrow'] ?? '';
  $lead = $data['lead'] ?? '';
@endphp
<section class="sec" style="padding-top: 130px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
  <div class="container text-center" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
    @if($eyebrow)
      <span class="floor-tag" style="margin-bottom: 1rem; display: inline-block;">@text($eyebrow)</span>
    @endif
    <h1 class="sec-title" style="font-size: 2.4rem; margin-bottom: 1rem;">
      @text($title) @if($highlight) <span>@text($highlight)</span> @endif
    </h1>
    @if($lead)
      <p class="sec-desc" style="font-size: 1.1rem; color: #64748b;">@text($lead)</p>
    @endif
  </div>
</section>
