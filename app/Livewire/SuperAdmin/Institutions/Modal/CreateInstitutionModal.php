<?php

namespace App\Livewire\SuperAdmin\Institutions\Modal;

use Livewire\Component;
use App\Models\Institution;
use App\Services\AuditLogService;

class CreateInstitutionModal extends Component
{
    public $name = '';
    public $domain = '';

    protected $rules = [
        'name' => 'required|string|max:255|unique:institutions,name',
        'domain' => 'required|string|max:255|unique:institutions,domain',
    ];

    public function createInstitution()
    {
        $this->validate();

        $institution = Institution::create([
            'name' => $this->name,
            'domain' => $this->domain,
        ]);

        // Assign institution_id to users with matching email domain (SQLite compatible)
        $domain = strtolower($institution->domain);
        $users = \App\Models\User::whereRaw('LOWER(email) LIKE ?', ['%@' . $domain])
            ->get();

        $userIds = [];
        foreach ($users as $user) {
            $emailDomain = strtolower(substr(strrchr($user->email, "@"), 1));
            if ($emailDomain === $domain) {
                $user->institution_id = $institution->id;
                $user->save();
                $userIds[] = $user->id;
            }
        }

        // Log out all affected users by deleting their sessions
        if (!empty($userIds)) {
            \DB::table('sessions')->whereIn('user_id', $userIds)->delete();
        }

        // Audit log the institution creation - Fixed to not pass array as string
        AuditLogService::logCreate(
            resourceType: 'Institution',
            resourceId: $institution->id,
            data: [
                'name' => $institution->name,
                'domain' => $institution->domain,
                'users_assigned' => count($userIds),
            ],
            message: "Created institution: '{$institution->name}' with domain '{$institution->domain}' and assigned " . count($userIds) . " user(s)"
        );

        $this->dispatch('institution-created');
        $this->dispatch('refresh-institution-index');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', name: 'create-institution-modal');
        $this->name = '';
        $this->domain = '';
    }

    public function render()
    {
        return view('livewire.super-admin.institutions.modal.create-institution-modal');
    }
}
