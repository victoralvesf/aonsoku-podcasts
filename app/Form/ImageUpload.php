<?php

namespace App\Form;

use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUpload extends FileUpload
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->image()
            ->disk('public')
            ->visibility('public')
            ->required(fn(string $operation): bool => $operation === 'create')
            ->mutateDehydratedStateUsing(function (?string $state): ?string {
                if (blank($state)) {
                    return null;
                }

                if (Str::startsWith($state, ['http://', 'https://'])) {
                    return $state;
                }

                return Storage::disk('public')->url($state);
            })
            ->afterStateHydrated(static function (BaseFileUpload $component, string|array|null $state) {
                $appBaseUrl = rtrim(Storage::disk('public')->url('/'), '/');

                if (!blank($state) && Str::startsWith($state, needles: $appBaseUrl)) {
                    $state = Str::after($state, "$appBaseUrl/");

                    $component->state([((string) Str::uuid()) => $state]);
                    return;
                }

                $component->state([]);
            });
    }
}
