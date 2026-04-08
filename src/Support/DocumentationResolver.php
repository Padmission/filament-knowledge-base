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

        return static::resolveControllerFromReferer($request);
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
