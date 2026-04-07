<div>
    <label for="member-select" class="block text-xs font-medium text-zinc-700 dark:text-zinc-200 mb-1">Vælg medlem</label>
    <select id="member-select" wire:model="selectedMemberId" class="w-full rounded border-zinc-300 dark:bg-zinc-800 dark:text-zinc-100">
        @foreach($members as $member)
            <option value="{{ $member->id }}">{{ $member->firstname ?? $member->name }}</option>
        @endforeach
    </select>
</div>
