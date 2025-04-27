<?php

namespace App\Livewire\Surveys;

use Livewire\Component;

class FormBuilder extends Component
{
    // The array of form fields, each field is an associative array with the field details
    public $fields = [];

    // To track what type of field to add (e.g., text, radio, etc.)
    public $newFieldType = 'text'; 

    // Method to add a new field
    public function addField()
    {
        
        $this->fields[] = [
            'type' => $this->newFieldType, // type of field like text, radio, etc.
            'label' => '', // default empty label
            'value' => '', // default empty value
        ];
    }

    // Method to remove a field by its index
    public function removeField($index)
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields); // Re-index array
    }

    // Method to update field data
    public function updateField($index, $data)
    {
        $this->fields[$index] = $data;
    }

    public function render()
    {
        return view('livewire.surveys.form-builder');
    }
}
