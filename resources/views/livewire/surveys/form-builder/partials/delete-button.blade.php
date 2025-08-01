<button
    @if($context === 'page' || (isset($questionType) && $questionType === 'page'))
        wire:click.prevent="$dispatch('confirmDelete', { type: '{{ isset($type) ? $type : 'page' }}', id: {{ $id }}, action: '{{ $action }}' })"
    @else
        wire:click.stop="@if(isset($type)){{ $action }}('{{ $type }}', {{ $id }})@else{{ $action }}({{ $id }})@endif"
    @endif
    class="p-1.5 text-red-500 hover:text-red-600 focus:outline-none flex items-center justify-center"
    title="{{ $context === 'page' ? 'Delete Page' : 'Remove Question' }}"
    wire:loading.attr="disabled"
    wire:target="@if(isset($type)){{ $action }}('{{ $type }}', {{ $id }})@else{{ $action }}({{ $id }})@endif"
>
    <span wire:loading.remove wire:target="@if(isset($type)){{ $action }}('{{ $type }}', {{ $id }})@else{{ $action }}({{ $id }})@endif">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
        </svg>
    </span>
    <span wire:loading wire:target="@if(isset($type)){{ $action }}('{{ $type }}', {{ $id }})@else{{ $action }}({{ $id }})@endif">
          <svg class="animate-spin h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </span>
</button>
