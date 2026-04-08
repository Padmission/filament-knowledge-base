@use(Guava\FilamentKnowledgeBase\Facades\KnowledgeBase)

@pushOnce('styles')
<style>
    .gu-kb-article img {
        cursor: zoom-in;
        transition: opacity 0.15s ease;
    }
    .gu-kb-article img:hover {
        opacity: 0.85;
    }
    .gu-kb-lightbox-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background-color: rgba(0, 0, 0, 0.8);
    }
    .dark .gu-kb-lightbox-overlay {
        background-color: rgba(0, 0, 0, 0.9);
    }
    .gu-kb-lightbox-overlay img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        cursor: default;
    }
    .gu-kb-lightbox-close {
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        background: none;
        border: none;
        color: white;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.15s ease;
        padding: 0.25rem;
    }
    .gu-kb-lightbox-close:hover {
        opacity: 1;
    }
</style>
@endPushOnce

<div
    x-data="{
        lightboxOpen: false,
        lightboxSrc: '',
        lightboxAlt: '',
        openLightbox(src, alt) {
            this.lightboxSrc = src;
            this.lightboxAlt = alt || '';
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },
    }"
    x-init="$el.querySelectorAll('.gu-kb-article img').forEach(img => {
        img.addEventListener('click', () => openLightbox(img.getAttribute('src'), img.getAttribute('alt')))
    })"
>
    <article
        {{ $attributes->class([
            'gu-kb-article',
            '[&_ul]:list-[revert] [&_ol]:list-[revert] [&_ul]:ml-4 [&_ol]:ml-4' => ! KnowledgeBase::panel()->shouldDisableDefaultClasses(),
        ]) }}
        x-ignore
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('anchors-component', 'guava/filament-knowledge-base') }}"
        x-data="anchorsComponent()"
    >
        {{ $slot }}
    </article>

    <template x-teleport="body">
        <div
            x-show="lightboxOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click.self="closeLightbox()"
            x-on:keydown.escape.window="closeLightbox()"
            class="gu-kb-lightbox-overlay"
            x-cloak
            role="dialog"
            aria-modal="true"
            :aria-label="lightboxAlt"
            style="display: none;"
        >
            <button x-on:click="closeLightbox()" class="gu-kb-lightbox-close" aria-label="Close">&times;</button>
            <img
                :src="lightboxSrc"
                :alt="lightboxAlt"
                x-on:click.stop
            />
        </div>
    </template>
</div>
