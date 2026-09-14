<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepositRequestResource\Pages;
use App\Models\DepositRequest;
use App\Models\WalletTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class DepositRequestResource extends Resource
{
    protected static ?string $model = DepositRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Deposit Requests';
    protected static ?string $modelLabel = 'Deposit Request';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sales_id')->relationship('sales', 'name')->disabled(),
            Forms\Components\TextInput::make('amount')->numeric()->prefix('Rp')->disabled(),
            Forms\Components\Textarea::make('admin_note'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('sales.name')->searchable(),
                Tables\Columns\TextColumn::make('amount')->money('IDR')->sortable(),
                Tables\Columns\ImageColumn::make('proof_path')->label('Bukti Transfer')->disk('public'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i')->label('Diajukan'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->visible(fn (DepositRequest $record) => $record->status === 'pending')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function (DepositRequest $record) {
                        DB::transaction(function () use ($record) {
                            $sales = $record->sales;
                            $sales->increment('wallet_balance', $record->amount);

                            WalletTransaction::create([
                                'user_id' => $sales->id,
                                'type' => 'credit',
                                'amount' => $record->amount,
                                'balance_after' => $sales->fresh()->wallet_balance,
                                'description' => "Top-up disetujui (Deposit #{$record->id})",
                                'reference_type' => DepositRequest::class,
                                'reference_id' => $record->id,
                            ]);

                            $record->update([
                                'status' => 'approved',
                                'reviewed_by' => auth()->id(),
                                'reviewed_at' => now(),
                            ]);
                        });

                        Notification::make()->title('Deposit disetujui, saldo Sales bertambah.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->visible(fn (DepositRequest $record) => $record->status === 'pending')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('admin_note')->label('Alasan penolakan')->required(),
                    ])
                    ->action(function (DepositRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_note' => $data['admin_note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()->title('Deposit ditolak.')->warning()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepositRequests::route('/'),
        ];
    }
}
