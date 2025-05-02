<div class="space-y-4 p-4">
    <div>
        <label class="block font-semibold mb-1">Target Respondents</label>
        <input type="number" wire:model.lazy="target_respondents" class="w-full border rounded px-3 py-2" min="1" />
    </div>
    <div>
        <label class="block font-semibold mb-1">Start Date</label>
        <input type="date" wire:model.lazy="start_date" class="w-full border rounded px-3 py-2" />
    </div>
    <div>
        <label class="block font-semibold mb-1">End Date</label>
        <input type="date" wire:model.lazy="end_date" class="w-full border rounded px-3 py-2" />
    </div>
    <div>
        <label class="block font-semibold mb-1">Points Allocated</label>
        <input type="number" wire:model.lazy="points_allocated" class="w-full border rounded px-3 py-2" min="0" />
    </div>
    <div class="text-green-600" wire:loading.remove>
        Changes are saved automatically.
    </div>
</div>
