<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use App\Models\Building;
use App\Models\Department;
use App\Models\Role;

class CompleteProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.complete-profile';
    protected static ?string $slug = 'complete-profile';

    public $building_id;
    public $department_id;
    public $role_id;

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('building_id')
                ->label('Building')
                ->options(Building::pluck('name', 'id'))
                ->required(),
            Forms\Components\Select::make('department_id')
                ->label('Department')
                ->options(Department::pluck('name', 'id'))
                ->required(),
        ];
    }

    public function mount(): void
    {
        $staffRole = Role::where('code', 'Staff')->first();
        $this->role_id = $staffRole->id;

        if (auth()->user()->building_id && auth()->user()->department_id) {
            $this->redirect('/app');
        }
    }

    public function submit()
    {
        $this->validate([
            'building_id' => 'required',
            'department_id' => 'required',
        ]);

        $staffRole = Role::where('code', 'Staff')->first();

        auth()->user()->update([
            'building_id' => $this->building_id,
            'department_id' => $this->department_id,
            'role_id' => $staffRole->id,
        ]);

        $this->redirect('/app');
    }
}
