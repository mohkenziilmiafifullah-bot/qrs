<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Sales';
    protected static ?string $modelLabel = 'Sales';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'sales');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('phone')->tel()->maxLength(20),
            Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $context) => $context === 'create')
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->options(['pending' => 'Pending', 'active' => 'Active', 'blocked' => 'Blocked'])
                ->default('active')
                ->required(),
            Forms\Components\TextInput::make('wallet_balance')
                ->numeric()
                ->prefix('Rp')
                ->default(0)
                ->helperText('Gunakan menu Deposit Request untuk penambahan saldo dari top-up. Field ini untuk koreksi manual.'),
            Forms\Components\Hidden::make('role')->default('sales'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('wallet_balance')->money('IDR')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'warning' => 'pending', 'danger' => 'blocked']),
                Tables\Columns\TextColumn::make('qrs_count')->counts('qrs')->label('QR Aktif'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->label('Bergabung'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'active' => 'Active', 'blocked' => 'Blocked']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
