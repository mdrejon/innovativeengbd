@extends('layouts.frontend')

@section('meta_title', $post->meta_title ?: $post->title.' | '.Cms::setting('brand_name', 'Innovative Engineering'))
@section('meta_description', $post->meta_description ?: $post->excerpt)
@if($post->featured_image)
  @section('og_image', $post->featured_image)
@endif

@section('content')
<section class="sec" style="padding-top: 140px;">
  <div class="container" style="max-width: 860px;">
    <div style="margin-bottom: 2rem;">
      <a href="{{ url('/') }}" class="floor-tag" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Home
      </a>
      @if($post->category)
        <span class="badge" style="margin-left: 0.5rem; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">{{ $post->category }}</span>
      @endif
      <h1 class="sec-title" style="margin-top: 1rem; font-size: 2.2rem; line-height: 1.25;">{{ $post->title }}</h1>
      <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: #64748b; margin-top: 0.5rem;">
        <span><i class="fas fa-user"></i> {{ $post->author_name }}</span>
        <span><i class="fas fa-clock"></i> {{ $post->read_time }}</span>
        <span><i class="fas fa-calendar"></i> {{ $post->published_at ? $post->published_at->format('M d, Y') : '' }}</span>
      </div>
    </div>

    @if($post->featured_image)
      <div style="margin-bottom: 2rem; border-radius: 1rem; overflow: hidden;">
        <img src="{{ Cms::asset($post->featured_image) }}" alt="{{ $post->title }}" style="width: 100%; max-height: 420px; object-fit: cover;">
      </div>
    @endif

    <div class="blog-body" style="font-size: 1.05rem; line-height: 1.8; color: #334155;">
      {!! $post->content !!}
    </div>

    @if($recentPosts->isNotEmpty())
      <div style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
        <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;">Recent Articles</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
          @foreach($recentPosts as $recent)
            <article style="background: #f8fafc; padding: 1.25rem; border-radius: 0.75rem; border: 1px solid #e2e8f0;">
              <span style="font-size: 0.75rem; font-weight: 700; color: #0284c7; text-transform: uppercase;">{{ $recent->category }}</span>
              <h4 style="margin: 0.5rem 0; font-size: 1rem;"><a href="{{ route('blog.show', $recent->slug) }}" style="color: #0f172a; text-decoration: none;">{{ $recent->title }}</a></h4>
              <p style="font-size: 0.85rem; color: #64748b; margin: 0;">{{ Str::limit($recent->excerpt, 90) }}</p>
            </article>
          @endforeach
        </div>
      </div>
    @endif
  </div>
</section>
@endsection
