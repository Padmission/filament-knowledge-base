<article
    {{ $attributes->class([
    'gu-kb-article',
]) }}
    x-ignore
    x-load
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('anchors-component', 'guava/filament-knowledge-base') }}"
    x-data="anchorsComponent()"
>
    {{ $slot }}
</article>
