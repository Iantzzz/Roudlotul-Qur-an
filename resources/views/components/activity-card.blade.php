@props([
    'activity',
    'showLink' => true,
])

<article class="activity-card">
    <div class="activity-card__media">
        <x-site-image :name="$activity['image']" class="activity-card__image" />
        <span class="activity-card__number" aria-hidden="true">{{ $activity['number'] }}</span>
    </div>

    <div class="activity-card__body">
        <span class="eyebrow">{{ $activity['label'] }}</span>
        <h3>{{ $activity['title'] }}</h3>
        <p>{{ $activity['text'] }}</p>

        @if ($showLink)
            <a class="text-link" href="{{ route('kegiatan') }}">
                Lihat program
                <x-site-icon name="arrow-up-right" :size="16" />
            </a>
        @endif
    </div>
</article>
