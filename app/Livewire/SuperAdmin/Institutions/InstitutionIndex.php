<?php

namespace App\Livewire\SuperAdmin\Institutions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Institution;

class InstitutionIndex extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $selectedInstitutionId = null;
    public $createModalKey;
    public $manageModalKey;

    protected $queryString = ['searchTerm'];

    protected $listeners = [
        'refresh-institution-index' => 'refreshIndex',
    ];

    public function mount()
    {
        $this->createModalKey = uniqid();
        $this->manageModalKey = uniqid();
    }

    public function openCreateModal()
    {
        $this->createModalKey = uniqid();
        $this->dispatch('open-modal', name: 'create-institution-modal');
    }

    public function refreshIndex()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Institution::query();

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('domain', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $institutions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.super-admin.institutions.institution-index', [
            'institutions' => $institutions,
            'selectedInstitutionId' => $this->selectedInstitutionId,
            'createModalKey' => $this->createModalKey,
            'manageModalKey' => $this->manageModalKey,
        ]);
    }
}
