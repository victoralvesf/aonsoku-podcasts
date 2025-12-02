<?php

namespace App\Form;

use Exception;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUpload extends FileUpload
{
    /**
     * @throws Exception
     */
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
                if (blank($state)) {
                    return $component->state([]);
                }

                $storageBaseUrl = Storage::disk('public')->url('');
                $uuid           = Str::uuid()->toString();

                if (is_string($state) && Str::startsWith($state, $storageBaseUrl)) {
                    $state = Str::after($state, $storageBaseUrl);

                    return $component->state([ $uuid => $state ]);
                }

                if (is_string($state) && Str::startsWith($state, ['http://', 'https://'])) {
                    return $component->state([ $uuid => $state ]);
                }

                return $component->state([]);
            });
    }
}
