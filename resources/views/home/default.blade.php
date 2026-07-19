@extends('layouts.simple')

@section('body')

    <div class="container px-xl py-s flex-container-row gap-l wrap justify-space-between">
        <div class="icon-list inline block">
            @include('home.parts.expand-toggle', ['classes' => 'text-muted text-link', 'target' => '.entity-list.compact .entity-item-snippet', 'key' => 'home-details'])
        </div>
        <div>
            <div class="icon-list inline block">
                @include('common.dark-mode-toggle', ['classes' => 'text-muted icon-list-item text-link'])
            </div>
        </div>
    </div>

    <div class="container" id="home-default">
        <div class="grid third gap-x-xxl no-row-gap">
            <div>
                @if(count($draftPages) > 0)
                    <div id="recent-drafts" class="card mb-xl">
                        <h3 class="card-title">{{ trans('entities.my_recent_drafts') }}</h3>
                        <div class="px-m">
                            @include('entities.list', ['entities' => $draftPages, 'style' => 'compact'])
                        </div>
                    </div>
                @endif

                <div id="{{ auth()->check() ? 'recently-viewed' : 'recent-books' }}" class="card mb-xl">
                    <h3 class="card-title">{{ trans('entities.' . (auth()->check() ? 'my_recently_viewed' : 'books_recent')) }}</h3>
                    <div class="px-m">
                        @include('entities.list', [
                        'entities' => $recents,
                        'style' => 'compact',
                        'emptyText' => auth()->check() ? trans('entities.no_pages_viewed') : trans('entities.books_empty')
                        ])
                    </div>
                </div>

                @if(auth()->check() && count($readingProgressItems) > 0)
                    <div id="reading-progress-tracker" class="card mb-xl">
                        <h3 class="card-title">{{ trans('entities.continue_reading') }}</h3>
                        <div class="px-m">
                            @foreach($readingProgressItems as $item)
                                <div class="entity-list-item compact mb-s">
                                    <a href="{{ $item['entity']->getUrl() }}" class="entity-list-item-link">
                                        <div class="entity-item-name break-text">{{ $item['entity']->name }}</div>
                                        <div class="text-muted text-small">{{ $item['entity']->book->name }}</div>
                                        @if($item['entity']->chapter)
                                            <div class="text-muted text-small">{{ $item['entity']->chapter->name }}</div>
                                        @endif
                                        <div class="mt-xs">
                                            <div class="progress-bar" style="height: 6px; background: #e9ecef; border-radius: 999px; overflow: hidden;">
                                                <div style="width: {{ $item['progress'] }}%; height: 100%; background: #2f80ed; border-radius: 999px;"></div>
                                            </div>
                                            <div class="text-small text-muted mt-xxs">
                                                @if($item['progress'] >= 100)
                                                    <span class="text-success">{{ trans('entities.completed') }} ✓</span>
                                                @else
                                                    {{ $item['progress'] }}%
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div>
                @if(count($favourites) > 0)
                    <div id="top-favourites" class="card mb-xl">
                        <h3 class="card-title">{{ trans('entities.my_most_viewed_favourites') }}</h3>
                        <div class="px-m">
                            @include('entities.list', [
                            'entities' => $favourites,
                            'style' => 'compact',
                            ])
                        </div>
                        <a href="{{ url('/favourites')  }}" class="card-footer-link">{{ trans('common.view_all') }}</a>
                    </div>
                @endif

                <div id="recent-pages" class="card mb-xl">
                    <h3 class="card-title">{{ trans('entities.recently_updated_pages') }}</h3>
                    <div id="recently-updated-pages" class="px-m">
                        @include('entities.list', [
                        'entities' => $recentlyUpdatedPages,
                        'style' => 'compact',
                        'emptyText' => trans('entities.no_pages_recently_updated'),
                        ])
                    </div>
                    @if(count($recentlyUpdatedPages) > 0)
                        <a href="{{ url("/pages/recently-updated") }}" class="card-footer-link">{{ trans('common.view_all') }}</a>
                    @endif
                </div>
            </div>

            <div>
                <div id="recent-activity" class="card mb-xl">
                    <h3 class="card-title">{{ trans('entities.recent_activity') }}</h3>
                    <div class="px-m">
                        @include('common.activity-list', ['activity' => $activity])
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop
