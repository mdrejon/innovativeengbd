@if(!empty($links))
  <div class="socials">
    @foreach($links as $link)
      <a href="{{ Cms::url($link['url'] ?? '#') }}" target="_blank" rel="noopener" aria-label="{{ $link['label'] ?? '' }}">
        <i class="{{ $link['icon'] ?? 'fas fa-link' }}"></i>
      </a>
    @endforeach
  </div>
@endif
