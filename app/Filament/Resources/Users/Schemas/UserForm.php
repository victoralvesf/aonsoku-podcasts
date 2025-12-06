<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Tenant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Info')
                    ->columnSpanFull()
                    ->icon(Heroicon::UserCircle)
                    ->columns(2)
                    ->schema([
                        TextInput::make('username')
                            ->required(),

                        Select::make('tenant_id')
                            ->label('Server URL')
                            ->relationship('tenant', 'server_url')
                            ->required()
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('server_url')
                                    ->label('Server URL')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $tenant = Tenant::create([
                                    'server_url' => $data['server_url'],
                                ]);

                                return $tenant->getKey();
                            }),
                    ]),
            ]);
    }
}
