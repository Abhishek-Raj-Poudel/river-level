<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Account Information')
                    ->description('Basic user account details')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('contact_number')
                            ->label('Contact Number')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('e.g., +1 (555) 123-4567')
                            ->columnSpan(1),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->rule(Password::default())
                            ->columnSpan(1)
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->same('password')
                            ->dehydrated(false)
                            ->columnSpan(1)
                            ->visible(fn (string $operation): bool => $operation === 'create'),

                        TextInput::make('new_password')
                            ->label('New Password')
                            ->password()
                            ->rule(Password::default())
                            ->dehydrated(false)
                            ->columnSpan(1)
                            ->visible(fn (string $operation): bool => $operation === 'edit'),

                        TextInput::make('new_password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->same('new_password')
                            ->dehydrated(false)
                            ->columnSpan(1)
                            ->visible(fn (string $operation): bool => $operation === 'edit'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->native(false)
                            ->seconds(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(3)
                    ->collapsible(),

                Section::make('Location')
                    ->description('User location coordinates')
                    ->icon('heroicon-o-map-pin')
                    ->columns(2)
                    ->schema([
                        TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->extraAttributes(['step' => 'any'])
                            ->placeholder('e.g., 27.7172')
                            ->minValue(-90)
                            ->maxValue(90)
                            ->columnSpan(1),

                        TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->extraAttributes(['step' => 'any'])
                            ->placeholder('e.g., 85.3240')
                            ->minValue(-180)
                            ->maxValue(180)
                            ->columnSpan(1),
                    ])
                    ->columnSpan(3)
                    ->collapsible(),
            ]);
    }
}
