<x-filament-panels::page>
    <x-filament-panels::form wire:submit="submit">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6">
            Change Password
        </x-filament::button>
    </x-filament-panels::form>
</x-filament-panels::page>