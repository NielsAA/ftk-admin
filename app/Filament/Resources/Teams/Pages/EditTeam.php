<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Actions\SwapTeamStripePriceAction;
use App\Filament\Resources\Teams\TeamResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditTeam extends EditRecordRedirectToIndex
{
    protected static string $resource = TeamResource::class;

    protected function getActiveSubscriberCount(): int
    {
        return $this->record->membersOfTeam()
            ->whereNull('left_at')
            ->whereNotNull('stripe_subscription_id')
            ->where('stripe_subscription_id', '!=', '')
            ->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeStripePriceId')
                ->label('AEndr Stripe Price ID')
                ->icon('heroicon-o-credit-card')
                ->color('warning')
                ->visible(fn (): bool => filled($this->record->stripe_price_id))
                ->modalHeading('AEndr Stripe Price ID')
                ->modalDescription(function (): string {
                    $subscriberCount = $this->getActiveSubscriberCount();

                    if ($subscriberCount < 1) {
                        return 'Indtast nyt Stripe Price ID. Holdet har ingen aktive Stripe-abonnenter at opdatere lige nu.';
                    }

                    return "Indtast nyt Stripe Price ID. Dette vil opdatere {$subscriberCount} aktive Stripe-abonnenter paa holdet.";
                })
                ->modalSubmitActionLabel('Opdater Stripe Price ID')
                ->form([
                    TextInput::make('stripe_price_id')
                        ->label('Nyt Stripe Price ID')
                        ->required()
                        ->placeholder('price_...')
                        ->default($this->record->stripe_price_id),
                ])
                ->action(function (array $data, SwapTeamStripePriceAction $swapTeamStripePriceAction): void {
                    $result = $swapTeamStripePriceAction->execute($this->record, $data['stripe_price_id']);

                    $this->record->refresh();
                    $this->refreshFormData(['stripe_price_id']);

                    Notification::make()
                        ->title('Stripe Price ID opdateret')
                        ->body("Opdateret: {$result['updated']}, sprunget over: {$result['skipped']}, fejl: {$result['failed']}")
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
