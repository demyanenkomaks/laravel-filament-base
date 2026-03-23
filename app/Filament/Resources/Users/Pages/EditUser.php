<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use TomatoPHP\FilamentUsers\Facades\FilamentUser;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
        ];
    }
}
