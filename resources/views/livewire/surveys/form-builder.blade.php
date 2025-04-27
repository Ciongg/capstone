

<div>
    @section('content')
    <h2 class="text-2xl font-semibold mb-4">Create Your Survey Form</h2>

    <!-- Field Type Selector -->
    <div class="mb-4">
        <label for="field-type" class="block text-lg">Choose Field Type</label>
        <select wire:model.live="newFieldType" id="field-type" class="mt-2 p-2 border rounded">
            <option value="text">Text</option>
            <option value="radio">Radio Button</option>
            <option value="textarea">Textarea</option>
        </select>
    </div>

    <!-- Add New Field Button -->
    <button wire:click="addField" class="px-4 py-2 bg-blue-500 text-white rounded mb-4">
        Add Field
    </button>

    <!-- Form Fields -->
    <div>
        @foreach ($fields as $index => $field)
            <div class="mb-4 p-4 border rounded bg-gray-50">
                <!-- Label Input -->
                <input wire:model="fields.{{ $index }}.label" type="text" class="w-full p-2 mb-2 border rounded" placeholder="Field Label">

                <!-- Field Type Rendering Based on Selection -->
                @if ($field['type'] == 'text')
                    <input wire:model="fields.{{ $index }}.value" type="text" class="w-full p-2 mb-2 border rounded" placeholder="Enter text">
                @elseif ($field['type'] == 'radio')
                    <div>
                        <input wire:model="fields.{{ $index }}.value" type="radio" value="Option 1"> Option 1
                        <input wire:model="fields.{{ $index }}.value" type="radio" value="Option 2"> Option 2
                    </div>
                @elseif ($field['type'] == 'textarea')
                    <textarea wire:model="fields.{{ $index }}.value" class="w-full p-2 mb-2 border rounded" placeholder="Enter your text"></textarea>
                @endif

                <!-- Remove Button -->
                <button wire:click="removeField({{ $index }})" class="text-red-500 hover:text-red-700">Remove Field</button>
            </div>
        @endforeach
    </div>

    <!-- Submit Button -->
    <button wire:click="saveForm" class="px-4 py-2 bg-green-500 text-white rounded mt-4">
        Save Form
    </button>
    @endsection
</div>