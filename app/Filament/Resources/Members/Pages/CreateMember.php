<?php

namespace App\Filament\Resources\Members\Pages;

use App\Actions\SyncMemberTeamsFromSelectionAction;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;

class CreateMember extends CreateRecordRedirectToIndex
{
    protected static string $resource = MemberResource::class;

    /** @var array<int> */
    protected array $selectedTeamIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedTeamIds = array_map('intval', $data['team_ids'] ?? []);

        unset($data['team_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(SyncMemberTeamsFromSelectionAction::class)->execute($this->record, $this->selectedTeamIds);
    }
}
