<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ config('site.brand.name') }} — Beranda">
            <img
                class="brand-logo"
                src="{{ asset(config('site.brand.logo')) }}"
                alt=""
                width="194"
                height="189"
            >
            <span class="brand-text">
                <strong>{{ config('site.brand.name') }}</strong>
                <small>{{ config('site.brand.tagline') }}</small>
            </span>
        </a>

        <button
            class="nav-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="site-navigation"
            aria-label="Buka menu"
            data-nav-toggle
        >
            <x-site-icon name="menu" :size="25" class="nav-toggle__open" />
            <x-site-icon name="close" :size="25" class="nav-toggle__close" />
        </button>

        <nav class="site-nav" id="site-navigation" aria-label="Navigasi utama" data-nav>
            <ul class="nav-list">
                @foreach (config('site.navigation') as $item)
                    @php
                        $isActive = request()->routeIs($item['route'])
                            || ($item['route'] === 'berita' && request()->routeIs('berita.*'));
                    @endphp

                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @class(['is-active' => $isActive])
                            @if ($isActive) aria-current="page" @endif
                        >
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <a
                class="btn btn-gold nav-cta"
                href="{{ route('pendaftaran') }}"
                @if (request()->routeIs('pendaftaran')) aria-current="page" @endif
            >
                Pendaftaran
                <x-site-icon name="arrow-right" :size="17" />
            </a>
        </nav>
    </div>
</header>
