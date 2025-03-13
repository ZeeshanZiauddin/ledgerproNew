<?php
namespace App\Livewire;

use Filament\Support\Enums\ActionSize;
use Livewire\Component;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ResourceCountComponent extends Component implements HasForms, HasActions
{
    use InteractsWithActions, InteractsWithForms;

    public string $resourceClass;
    public string $description;
    public string $color;
    public string $theme;
    public string $model;
    public int $count = 0;

    public function mount(string $resourceClass, string $description, string $color, string $theme): void
    {
        $this->resourceClass = $resourceClass;
        $this->description = $description;
        $this->model = $this->getModelFromResource($resourceClass);
        $this->count = $this->model::count();
        $this->color = $color;
        $this->theme = $theme;
    }

    /**
     * Fetch the model from the resource.
     */
    private function getModelFromResource(string $resourceClass): string
    {
        return $resourceClass::getModel();
    }

    /**
     * Create a new entry for the resource.
     */
    public function createAction(): CreateAction
    {
        return CreateAction::make('Create')
            ->icon('heroicon-m-plus-circle')
            ->button()
            ->hiddenLabel()
            ->model($this->model)
            ->size(ActionSize::Medium)
            ->color($this->theme)
            ->form($this->resourceClass::getFormSchema());
    }

    public function render()
    {
        return view('livewire.resource-count-component');
    }
}