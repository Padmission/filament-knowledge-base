<?php

namespace Guava\FilamentKnowledgeBase\Livewire;

use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Guava\FilamentKnowledgeBase\Actions\HelpAction;
use Guava\FilamentKnowledgeBase\Contracts\Documentable;
use Guava\FilamentKnowledgeBase\Facades\KnowledgeBase;
use Guava\FilamentKnowledgeBase\Support\DocumentationResolver;
use Livewire\Component;

class HelpMenu extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public array $documentation;

    protected bool $shouldOpenDocumentationInNewTab;

    public function mount(?array $documentation = null): void
    {
        $this->shouldOpenDocumentationInNewTab = Filament::getPlugin('guava::filament-knowledge-base')->shouldOpenDocumentationInNewTab();
        $this->documentation = $documentation ?? DocumentationResolver::resolve(request());
    }

    public function getDocumentation()
    {
        return collect($this->documentation)
            ->map(fn ($documentable) => KnowledgeBase::documentable($documentable))
        ;
    }

    public function actions(): array
    {
        return $this->getDocumentation()
            ->map(
                fn (Documentable $documentable) => HelpAction::forDocumentable($documentable)
                    ->openUrlInNewTab($this->shouldOpenDocumentationInNewTab)
            )
            ->all()
        ;
    }

    public function shouldShowAsMenu(): bool
    {
        return count($this->documentation) > 1;
    }

    public function getSingleAction(): HelpAction
    {
        return HelpAction::forDocumentable($this->getDocumentation()->first())
            ->generic()
            ->labeledFrom('md')
            ->openUrlInNewTab($this->shouldOpenDocumentationInNewTab)
        ;
    }

    public function getMenuAction(): ActionGroup
    {
        return ActionGroup::make($this->actions())
            ->label(__('filament-knowledge-base::translations.help'))
            ->icon('heroicon-o-question-mark-circle')
            ->iconSize('lg')
            ->color('gray')
            ->button()
            ->labeledFrom('md')
        ;
    }

    public function render()
    {
        return view('filament-knowledge-base::livewire.help-menu');
    }
}
