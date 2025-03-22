<?php

namespace Guava\FilamentKnowledgeBase\Filament\Resources;

use Filament\Resources\Resource;
use Guava\FilamentKnowledgeBase\Facades\KnowledgeBase;
use Guava\FilamentKnowledgeBase\Filament\Pages\ViewDocumentation;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class DocumentationResource extends Resource
{
    public static function getModel(): string
    {
        return KnowledgeBase::model();
    }

    public static function getGloballySearchableAttributes(): array
    {
        $panel = KnowledgeBase::panel();

        if (method_exists($panel, 'getGloballySearchableAttributes')) {
            return $panel->getGloballySearchableAttributes();
        }

        return ['title', 'content'];
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPages(): array
    {
        return [
            'view' => ViewDocumentation::route('/{record?}'),
        ];
    }

    protected static bool $shouldRegisterNavigation = false;

    public static function getRoutePrefix(): string
    {
        return '';
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return ViewDocumentation::getUrl(['record' => $record], panel: KnowledgeBase::panelId());
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        $panel = KnowledgeBase::panel();

        $callback = $panel->getGlobalSearchResultTitleCallback();

        if ($callback) {
            return $callback($record);
        }

        return str($record->slug)
            ->replace('/', ' -> ')
            ;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $panel = KnowledgeBase::panel();

        $callback = $panel->getGlobalSearchResultDetailsCallback();

        if ($callback) {
            return $callback($record);
        }
        
        return [];
    }

    public static function resolveRecordRouteBinding(int | string $key): ?Model
    {
        // TODO: First try to load it from a standalone (App/Docs) class
        $record = parent::resolveRecordRouteBinding($key);

        if (! $record?->isRegistered()) {
            return null;
        }

        return $record;
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-knowledge-base::translations.knowledge-base');
    }
}
