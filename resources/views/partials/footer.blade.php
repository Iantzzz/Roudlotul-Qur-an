<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <a class="brand brand--footer" href="{{ route('home') }}">
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

            <p>{{ config('site.brand.description') }}</p>

            <x-temp-badge text="Situs dalam penyempurnaan" />
        </div>

        <div class="footer-column">
            <h2>Tautan Cepat</h2>
            <ul class="footer-links">
                <li><a href="{{ route('profil') }}">Profil</a></li>
                <li><a href="{{ route('kegiatan') }}">Program & Kegiatan</a></li>
                <li><a href="{{ route('berita') }}">Berita</a></li>
                <li><a href="{{ route('galeri') }}">Galeri</a></li>
                <li><a href="{{ route('pendaftaran') }}">Pendaftaran</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h2>Kontak</h2>
            <ul class="footer-contact">
                <li>
                    <x-site-icon name="map-pin" :size="18" />
                    <span>{{ config('site.contact.address') }}</span>
                </li>
                <li>
                    <x-site-icon name="phone" :size="18" />
                    <span>{{ config('site.contact.phone') }}</span>
                </li>
                <li>
                    <x-site-icon name="mail" :size="18" />
                    <span>{{ config('site.contact.email') }}</span>
                </li>
                <li>
                    <x-site-icon name="clock" :size="18" />
                    <span>{{ config('site.contact.hours') }}</span>
                </li>
            </ul>
        </div>

        <div class="footer-column">
            <h2>Media Sosial</h2>
            <ul class="social-list" aria-label="Media sosial Roudlotul Qur’an">
                @foreach (config('site.socials') as $social)
                    <li>
                        @if ($social['url'])
                            <a class="social-link" href="{{ $social['url'] }}" rel="noopener noreferrer" target="_blank">
                                <x-site-icon :name="$social['icon']" :size="18" />
                                {{ $social['label'] }}
                            </a>
                        @else
                            <span class="social-link social-link--disabled">
                                <x-site-icon :name="$social['icon']" :size="18" />
                                {{ $social['label'] }}
                                <small>Segera</small>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="container footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('site.brand.name') }}. Hak cipta dilindungi.</p>
        <p>Galeri, sejarah, visi-misi, dan pendaftaran masih menunggu data resmi.</p>
    </div>
</footer>
