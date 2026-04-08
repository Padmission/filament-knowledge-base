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

it('resolves documentation from the referer route during livewire update requests', function () {
    app('router')->get('/docs-page', TestDocumentationController::class);

    $request = Request::create('/livewire/update', 'POST', [], [], [], [
        'HTTP_REFERER' => 'http://localhost/docs-page',
    ]);

    expect(DocumentationResolver::resolve($request))->toBe(['test-docs']);
});
