@props([
    'node' => [],
    'depth' => 1,
])

@php
    /** @var array{unit: ?\App\Models\ManagementUnit, nama: string, members: iterable, children: array, depth: int, fallback: bool} $node */
    $maxDepth = \App\Support\ManagementStructure::MAX_DEPTH;
    $depthModifier = $depth <= $maxDepth ? "org-branch--depth-{$depth}" : 'org-branch--depth-overflow';
    $isFallback = (bool) ($node['fallback'] ?? false);
@endphp

<article class="org-branch {{ $depthModifier }} @if ($isFallback) org-branch--fallback @endif" @if ($depth === 1) data-reveal @endif>
    @if (isset($node['nama']) && $node['nama'] !== '')
        <h3 class="org-branch__nama">{{ $node['nama'] }}</h3>
    @endif

    @forelse ($node['members'] ?? [] as $member)
        <div class="org-member">
            <span class="org-member__role">{{ $member->jabatan }}</span>
            <p class="org-member__nama">{{ $member->nama }}</p>
        </div>
    @empty
        @if (empty($node['children']))
            <p class="org-branch__empty">Wadah ini belum berisi anggota.</p>
        @endif
    @endforelse

    @if (! empty($node['children']))
        <div class="org-children">
            @foreach ($node['children'] as $child)
                <x-management-node :node="$child" :depth="$depth + 1" />
            @endforeach
        </div>
    @endif
</article>
