@php
  $anchor = $data['anchor'] ?? 'contact';
  $floor = $data['floor'] ?? '7';
  $floorTitle = $data['floor_title'] ?? '07 Contact';
  $title = $data['title'] ?? 'Let\'s Talk About';
  $highlight = $data['title_highlight'] ?? 'Your Lift';
  $subtitle = $data['subtitle'] ?? 'Ask for a price, book a site inspection or report a problem — we usually reply within minutes.';
  $formTitle = $data['form_title'] ?? 'Send Us a Message';
  $formSubtitle = $data['form_subtitle'] ?? 'Fill in the form — we record your inquiry and get in touch immediately.';
  $mapUrl = $data['map_iframe_url'] ?? 'https://maps.google.com/maps?q=Barik%20Building%2C%20Sheikh%20Mujib%20Road%2C%20Agrabad%2C%20Chattogram&z=16&output=embed';
  $phone = Cms::setting('phone', '01714-216666');
  $phone2 = Cms::setting('phone2', '01829-984722');
  $email = Cms::setting('sales_email', 'palash_pe@yahoo.com');
  $headOffice = Cms::setting('head_office', 'Barik Building, 10 Sk. Mujib Road (2nd Floor), Agrabad C/A, Chattogram');
  $branchOffice = Cms::setting('branch_office', '63/1 VIP Road, Malibag, Dhaka-1217');
  $whatsappNumber = preg_replace('/[^0-9]/', '', Cms::setting('whatsapp_number', '8801714216666'));
  $messengerUrl = Cms::setting('messenger_url', 'https://m.me/innovativeengbd');
  $facebookUrl = Cms::setting('facebook_url', 'https://www.facebook.com/innovativeengbd');
@endphp
<section id="{{ $anchor }}" class="sec" data-floor="{{ $floor }}" data-name="Contact">
  <div class="container">
    <div class="sec-head" data-reveal="up">
      <span class="floor-tag">{!! $floorTitle !!}</span>
      <h2 class="sec-title">@text($title) <span>@text($highlight)</span></h2>
      <p class="sec-desc">@text($subtitle)</p>
    </div>

    <div class="contact-grid">
      <div class="c-cards">
        <a class="c-card" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" data-link="call" data-reveal="up">
          <span class="c-card__ico"><i class="fas fa-phone"></i></span>
          <h4>Call us</h4>
          <strong>@text($phone)</strong>
        </a>
        <a class="c-card" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone2) }}" data-link="call2" data-reveal="up" style="--d: 0.05s">
          <span class="c-card__ico"><i class="fas fa-mobile-screen"></i></span>
          <h4>Alternate number</h4>
          <strong>@text($phone2)</strong>
        </a>
        <a class="c-card c-card--wa" href="https://wa.me/{{ $whatsappNumber }}" data-link="whatsapp" target="_blank" rel="noopener" data-reveal="up" style="--d: 0.1s">
          <span class="c-card__ico"><i class="fab fa-whatsapp"></i></span>
          <h4>WhatsApp</h4>
          <strong>Chat with us</strong>
        </a>
        <a class="c-card c-card--ms" href="{{ $messengerUrl }}" data-link="messenger" target="_blank" rel="noopener" data-reveal="up" style="--d: 0.15s">
          <span class="c-card__ico"><i class="fab fa-facebook-messenger"></i></span>
          <h4>Messenger</h4>
          <strong>Message us</strong>
        </a>
        <a class="c-card c-card--fb" href="{{ $facebookUrl }}" data-link="facebook" target="_blank" rel="noopener" data-reveal="up" style="--d: 0.2s">
          <span class="c-card__ico"><i class="fab fa-facebook-f"></i></span>
          <h4>Facebook</h4>
          <strong>Follow our page</strong>
        </a>
        <a class="c-card" href="mailto:{{ $email }}" data-link="email" data-reveal="up" style="--d: 0.25s">
          <span class="c-card__ico"><i class="fas fa-envelope"></i></span>
          <h4>Email</h4>
          <strong>@text($email)</strong>
        </a>
        <div class="c-card c-card--wide" data-reveal="up" style="--d: 0.3s">
          <span class="c-card__ico"><i class="fas fa-location-dot"></i></span>
          <h4>Head office</h4>
          <address>@text($headOffice)</address>
        </div>
        <div class="c-card c-card--wide" data-reveal="up" style="--d: 0.35s">
          <span class="c-card__ico"><i class="fas fa-building"></i></span>
          <h4>Branch office</h4>
          <address>@text($branchOffice)</address>
        </div>
      </div>

      <div class="form-box" data-reveal="right">
        <h3>@text($formTitle)</h3>
        <p>@text($formSubtitle)</p>
        
        <div class="f-ok" id="formOk" role="status" @if(!session('contact_success')) style="display: none;" @endif>
          <i class="fas fa-circle-check"></i> {{ session('contact_success') ?: 'Thank you! Your inquiry has been received. Our team will contact you shortly.' }}
        </div>

        <form id="contactForm" action="{{ route('contact.submit') }}" method="POST" novalidate>
          @csrf
          <div class="f-row">
            <div class="f-grp">
              <label for="fName">Your name <b>*</b></label>
              <div class="f-ctl"><i class="fas fa-user"></i><input id="fName" name="name" type="text" autocomplete="name" placeholder="Full name" required /></div>
            </div>
            <div class="f-grp">
              <label for="fPhone">Mobile number <b>*</b></label>
              <div class="f-ctl">
                <i class="fas fa-phone"></i><input id="fPhone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="01XXXXXXXXX" pattern="[0-9+\-\s]{11,16}" title="Enter a valid mobile number, e.g. 01712345678" required />
              </div>
            </div>
          </div>
          <div class="f-grp">
            <label for="fService">Service needed</label>
            <div class="f-ctl">
              <i class="fas fa-list"></i>
              <select id="fService" name="service">
                <option value="">Select a service…</option>
                <option value="install">New lift installation</option>
                <option value="escalator">Escalator installation</option>
                <option value="repair">Lift repair &amp; servicing</option>
                <option value="amc">Monthly maintenance contract</option>
                <option value="parts">Spare parts</option>
                <option value="diagnosis">Fault diagnosis / technical support</option>
                <option value="product">Product price enquiry</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          <div class="f-grp">
            <label for="fMsg">Message <b>*</b></label>
            <div class="f-ctl"><i class="fas fa-message"></i><textarea id="fMsg" name="message" placeholder="Tell us briefly what you need…" required></textarea></div>
          </div>
          <button type="submit" class="btn btn--block" id="contactSubmitBtn">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
          <p class="f-note"><i class="fas fa-lock"></i> We keep your inquiry safe and contact you promptly.</p>
        </form>
      </div>
    </div>

    @if($mapUrl)
      <div class="map" data-reveal="up">
        <iframe
          title="Innovative Engineering office map"
          src="{{ $mapUrl }}"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
      </div>
    @endif
  </div>
</section>
