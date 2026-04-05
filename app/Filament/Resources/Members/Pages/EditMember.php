<?php

namespace App\Filament\Resources\Members\Pages;

use App\Actions\SyncMemberTeamsFromSelectionAction;
use App\Filament\Resources\Members\MemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class EditMember extends EditRecordRedirectToIndex
{
    protected static string $resource = MemberResource::class;

    /** @var array<int> */
    protected array $selectedTeamIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['team_ids'] = $this->record
            ->memberOfTeams()
            ->whereNull('left_at')
            ->pluck('team_id')
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedTeamIds = array_map('intval', $data['team_ids'] ?? []);

        unset($data['team_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        app(SyncMemberTeamsFromSelectionAction::class)->execute($this->record, $this->selectedTeamIds);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
