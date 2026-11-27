@php
  $title = $data['title'] ?? 'Lift stuck or not working?';
  $description = $data['description'] ?? 'Our emergency team reaches you within 30 minutes — day or night, all year.';
  $phone = $data['phone'] ?? Cms::setting('phone', '+8801714216666');
  $phoneLabel = $data['phone_label'] ?? Cms::setting('phone', '01714-216666');
  $whatsappUrl = $data['whatsapp_url'] ?? ('https://wa.me/'.preg_replace('/[^0-9]/', '', Cms::setting('whatsapp_number', '8801714216666')));
  $whatsappLabel = $data['whatsapp_label'] ?? 'WhatsApp';
@endphp
<div class="emg">
  <div class="container">
    <div class="emg__l" data-reveal="left">
      <div class="emg__ico"><i class="fas fa-phone"></i></div>
      <div>
        <h3>@text($title)</h3>
        <p>{!! nl2br(e($description)) !!}</p>
      </div>
    </div>
    <div class="emg__btns" data-reveal="right">
      <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn btn--white" data-link="call"><i class="fas fa-phone"></i> @text($phoneLabel)</a>
      <a href="{{ $whatsappUrl }}" class="btn btn--ghost" data-link="whatsapp" data-text="Emergency! My lift needs urgent service." target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> @text($whatsappLabel)</a>
    </div>
  </div>
</div>
