@props(['detailed' => false])

<div {{ $attributes->class(['program-catalog']) }}>
    @foreach (config('site.programs') as $group)
        <section class="program-catalog__group">
            <header class="program-catalog__header">
                <span class="program-catalog__eyebrow">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $group['eyebrow'] ?? 'Program' }}</span>
                <h3>{{ $group['title'] }}</h3>
                <p>{{ $group['description'] }}</p>
            </header>

            <ul class="program-list">
                @foreach ($group['items'] as $item)
                    <li class="program-item">
                        <span class="program-item__icon" aria-hidden="true">
                            <x-site-icon name="check" :size="15" />
                        </span>
                        <div class="program-item__content">
                            <h4>{{ $item['title'] }}</h4>
                            @if ($detailed)
                                <p>{{ $item['text'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</div>
