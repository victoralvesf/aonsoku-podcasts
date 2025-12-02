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
            ->openable()
            ->panelAspectRatio('1:1')
            ->disk('public')
            ->visibility('public')
            ->required(fn(string $operation): bool => $operation === 'create')
            // READ -> Ensure file upload load correctly external and internal URLs
            ->getUploadedFileUsing(function (?string $file) {
                if (blank($file)) {
                    return null;
                }

                if (Str::startsWith($file, ['http://', 'https://'])) {
                    return [
                        'name' => basename($file),
                        'url' => $file,
                        'size' => 0,
                    ];
                }

                return [
                    'name' => basename($file),
                    'url' => Storage::disk('public')->url($file),
                ];
            })
            // DB WRITE -> Ensure we store the full URL in the database
            ->mutateDehydratedStateUsing(function (?string $state): ?string {
                if (blank($state)) {
                    return null;
                }

                if (Str::startsWith($state, ['http://', 'https://'])) {
                    return $state;
                }

                return Storage::disk('public')->url($state);
            })
            // HYDRATE -> Convert stored URL back to relative storage path for the component
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
