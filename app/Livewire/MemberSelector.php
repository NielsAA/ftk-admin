<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class MemberSelector extends Component
{
    public $selectedMemberId;
    public $members = [];

    public function mount()
    {
        $user = Auth::user();
        $this->members = $user->members()->get();
        $this->selectedMemberId = session('selected_member_id', $user->member->id ?? null);
    }

    public function updatedSelectedMemberId($value)
    {
        session(['selected_member_id' => $value]);
        $this->dispatch('memberChanged', id: $value);
    }

    public function render()
    {
        return view('livewire.member-selector');
    }
}
