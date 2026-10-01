<?php

namespace App\Filament\Actions;

use Filament\Actions\CreateAction;
use Filament\Support\Icons\Heroicon;

class CreateIconAction extends CreateAction {
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->icon(Heroicon::Plus);
    }
}
