<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrResource\Pages;
use App\Models\Qr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QrResource extends Resource
{
    protected static ?string $model = Qr::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'QR Directory';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->disabled(),
            Forms\Components\Select::make('sales_id')
                ->relationship('sales', 'name')
                ->searchable()
                ->label('Sales Pemilik'),
            Forms\Components\TextInput::make('merchant_name')->maxLength(255),
            Forms\Components\TextInput::make('target_url')->url()->maxLength(65535),
            Forms\Components\Select::make('status')
                ->options([
                    'unassigned' => 'Unassigned',
                    'active' => 'Active',
                    'suspended' => 'Suspended',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('sales.name')->label('Sales')->searchable(),
                Tables\Columns\TextColumn::make('merchant_name')->searchable()->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'unassigned',
                        'success' => 'active',
                        'danger' => 'suspended',
                    ]),
                Tables\Columns\TextColumn::make('activated_at')->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('batch_reference')->label('Batch')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unassigned' => 'Unassigned',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Qr $record) => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (Qr $record) => $record->update(['status' => 'suspended'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQrs::route('/'),
            'edit' => Pages\EditQr::route('/{record}/edit'),
        ];
    }
}
