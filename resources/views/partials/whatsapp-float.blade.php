{{-- Tombol WhatsApp mengambang: mengarah ke narahubung pendaftaran pertama. --}}
<a class="wa-float"
   href="https://wa.me/{{ config('site.registration_contacts.0.wa') }}"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Chat WhatsApp {{ config('site.registration_contacts.0.name') }} — {{ config('site.registration_contacts.0.phone') }}"
   title="WhatsApp {{ config('site.registration_contacts.0.name') }} — {{ config('site.registration_contacts.0.phone') }}">
    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
</a>
