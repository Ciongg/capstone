@props(['title', 'name'])

<div 
    x-data = "{show : false , name : '{{$name}}'}"
    x-show = "show"
    x-on:open-modal.window = "show = ($event.detail.name === name)"
    x-on:close-modal.window = "show = false"
    x-on:keydown.escape.window = "show = false"
    style="display: none"
    x-transition
    class="fixed z-50 inset-0">
    <div x-on:click="show = false" class="fixed inset-0 bg-gray-900 opacity-20"></div>

    <div class="bg-white rounded-lg m-auto fixed inset-0 max-w-2xl max-h-[500px] p-2">

        <button x-on:click="$dispatch('close-modal')" class="text-red-800 font-bold">X</button>
      
            
            @if(isset($title))
                <div>
                    <h1 class="text-2xl font-bold mb-6">{{$title}}</h1>
                </div>
            @endif

            {{ $slot }}
    
      
    </div>

    
</div>