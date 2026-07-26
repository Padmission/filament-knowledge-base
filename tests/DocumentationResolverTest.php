<?php

use Guava\FilamentKnowledgeBase\Contracts\HasKnowledgeBase;
use Guava\FilamentKnowledgeBase\Support\DocumentationResolver;
use Illuminate\Http\Request;

class TestDocumentationController implements HasKnowledgeBase
{
    public static function getDocumentation(): array | string
    {
        return 'test-docs';
    }

    public function __invoke(): void {}
}

class TestUndocumentedController
{
    public function __invoke(): void {}
}

function requestForRoute(string $uri, array $server = []): Request
{
    $request = Request::create($uri, 'GET', [], [], [], $server);
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);

    return $request;
}

// Mirrors a real Livewire update request: the update endpoint resolves to Livewire's
// own (undocumented) controller, not null, and the X-Livewire header is present.
function livewireUpdateRequest(string $referer, string $headerValue = '1'): Request
{
    app('router')->post('/livewire/update', TestUndocumentedController::class);

    $request = Request::create('/livewire/update', 'POST', [], [], [], [
        'HTTP_REFERER' => $referer,
        'HTTP_X_LIVEWIRE' => $headerValue,
    ]);
    $route = app('router')->getRoutes()->match($request);
    $request->setRouteResolver(fn () => $route);

    return $request;
}

it('resolves documentation from the referer route during livewire update requests', function (string $headerValue) {
    app('router')->get('/docs-page', TestDocumentationController::class);

    $request = livewireUpdateRequest('http://localhost/docs-page', $headerValue);

    expect(DocumentationResolver::resolve($request))->toBe(['test-docs']);
})->with([
    // Livewire v4 sends X-Livewire: 1; v3 sends the header with an empty value.
    'livewire v4 header value' => '1',
    'livewire v3 header value' => '',
]);

it('resolves empty during livewire update requests when the referer page has no documentation', function () {
    app('router')->get('/no-docs-page', TestUndocumentedController::class);

    $request = livewireUpdateRequest('http://localhost/no-docs-page');

    expect(DocumentationResolver::resolve($request))->toBe([]);
});

it('resolves documentation from the current route on a full page load', function () {
    app('router')->get('/docs-page', TestDocumentationController::class);

    expect(DocumentationResolver::resolve(requestForRoute('/docs-page')))->toBe(['test-docs']);
});

it('does not borrow the referer documentation on a full page load of a page without documentation', function () {
    app('router')->get('/docs-page', TestDocumentationController::class);
    app('router')->get('/no-docs-page', TestUndocumentedController::class);

    $request = requestForRoute('/no-docs-page', [
        'HTTP_REFERER' => 'http://localhost/docs-page',
    ]);

    expect(DocumentationResolver::resolve($request))->toBe([]);
});
