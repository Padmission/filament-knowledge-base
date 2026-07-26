<?php

namespace Guava\FilamentKnowledgeBase\Support;

use Arr;
use Filament\Resources\Pages\Page;
use Guava\FilamentKnowledgeBase\Contracts\HasKnowledgeBase;
use Illuminate\Http\Request;
use Throwable;

class DocumentationResolver
{
    public static function resolve(Request $request): array
    {
        $controller = static::resolveControllerFromRequest($request);

        return Arr::wrap(match (true) {
            $controller instanceof HasKnowledgeBase => $controller::getDocumentation(),
            $controller instanceof Page && in_array(HasKnowledgeBase::class, class_implements($controller->getResource()) ?: []) => $controller->getResource()::getDocumentation(),
            default => [],
        });
    }

    protected static function resolveControllerFromRequest(Request $request): mixed
    {
        $controller = $request->route()?->getController();

        if (static::controllerHasDocumentation($controller)) {
            return $controller;
        }

        // Only Livewire update requests may borrow the referer's controller: there the
        // current route is the livewire/update endpoint, so the referer is the only way
        // back to the page being viewed. On a full page load, falling back would show a
        // doc-less page the previous page's documentation.
        if (static::isLivewireUpdateRequest($request)) {
            return static::resolveControllerFromReferer($request);
        }

        return null;
    }

    protected static function isLivewireUpdateRequest(Request $request): bool
    {
        return $request->hasHeader('X-Livewire');
    }

    protected static function resolveControllerFromReferer(Request $request): mixed
    {
        $referer = $request->headers->get('referer');

        if (blank($referer)) {
            return null;
        }

        try {
            $route = app('router')->getRoutes()->match(Request::create($referer, 'GET'));

            return $route->getController();
        } catch (Throwable) {
            return null;
        }
    }

    protected static function controllerHasDocumentation(mixed $controller): bool
    {
        if ($controller instanceof HasKnowledgeBase) {
            return true;
        }

        if (! $controller instanceof Page) {
            return false;
        }

        return in_array(HasKnowledgeBase::class, class_implements($controller->getResource()) ?: []);
    }
}
