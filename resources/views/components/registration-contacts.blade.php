@props(['compact' => false])

<div {{ $attributes->class(['contact-people', 'contact-people--compact' => $compact]) }}>
    @foreach (config('site.registration_contacts') as $contact)
        <article class="contact-person">
            <span class="contact-person__icon" aria-hidden="true">
                <x-site-icon name="phone" :size="20" />
            </span>

            <div class="contact-person__content">
                <span class="contact-person__role">{{ $contact['role'] }}</span>
                <h3>{{ $contact['name'] }}</h3>
                <a class="contact-person__phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">
                    {{ $contact['phone'] }}
                </a>
            </div>

            <a
                class="contact-person__action"
                href="https://wa.me/{{ $contact['wa'] }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Chat WhatsApp {{ $contact['name'] }}"
            >
                <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                <span>WhatsApp</span>
            </a>
        </article>
    @endforeach
</div>
