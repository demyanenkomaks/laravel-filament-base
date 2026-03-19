<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maksde\Helpers\Filament\Resources\Tables\Actions\ChangePassword;
use Maksde\Helpers\Filament\Resources\Tables\Actions\DeleteAction;
use Maksde\Helpers\Filament\Resources\Tables\Actions\EditAction;
use Maksde\Helpers\Filament\Resources\Tables\Actions\ImpersonateAction;
use Maksde\Helpers\Filament\Resources\Tables\Columns\BooleanIconColumn;
use Maksde\Helpers\Filament\Resources\Tables\Columns\CreateUpdateColumns;
use Maksde\Helpers\Filament\Resources\Tables\Columns\EmailColumn;
use Maksde\Helpers\Filament\Resources\Tables\Columns\IdColumn;
use TomatoPHP\FilamentUsers\Facades\FilamentUser;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IdColumn::make(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Имя'),
                EmailColumn::make('email', 'E-mail'),
                BooleanIconColumn::make('email_verified_at', 'Верифицирован')
                    ->state(static fn ($record): bool => (bool) $record->email_verified_at),
                TextColumn::make('roles.name')
                    ->formatStateUsing(static fn ($state) => str($state)->replace('_', ' ')->replace('-', ' ')->title())
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->toggleable()
                    ->badge()
                    ->label('Роли'),
                ...CreateUpdateColumns::make(),
            ])
            ->filters([
                Filter::make('verified')
                    ->schema([
                        Toggle::make('verified')
                            ->label('Верифицирован'),
                    ])
                    ->label('Верифицирован')
                    ->query(static fn (Builder $query, array $data): Builder => $query->when($data['verified'], static fn (
                        Builder $q,
                        $verified,
                    ) => $verified ? $q->whereNotNull('email_verified_at') : $q->whereNull('email_verified_at'))),
                SelectFilter::make('roles')
                    ->label('Роли')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                ChangePassword::make(),
                DeleteAction::make()
                    ->using(static function ($record, Action $action) {
                        $count = FilamentUser::getModel()::query()->count();
                        if ($count === 1) {
                            Notification::make()
                                ->title('Ошибка')
                                ->body('Вы не можете удалить последнего пользователя')
                                ->danger()
                                ->icon('heroicon-o-exclamation-triangle')
                                ->send();

                            return;
                        }

                        if (auth()->user()->id === $record->id) {
                            Notification::make()
                                ->title('Ошибка')
                                ->body('Вы не можете удалить себя')
                                ->danger()
                                ->icon('heroicon-o-exclamation-triangle')
                                ->send();

                            return;
                        }

                        $record->delete();
                        $action->success();

                        return redirect()->to(UserResource::getUrl('index'));
                    }),
                ImpersonateAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ]);
    }
}
