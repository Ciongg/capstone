<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white shadow-md rounded-lg p-8">
            <h1 class="text-3xl font-bold mb-8">{{ $survey->title }}</h1>
            <form wire:submit.prevent="submit">
                <div x-data="{ currentPage: 0 }">
                    @php $questionNumber = 1; @endphp
                    @foreach($survey->pages as $pageIndex => $page)
                        <div x-show="currentPage === {{ $pageIndex }}" x-cloak>
                            @if($page->title)
                                <h2 class="text-2xl font-semibold mb-2">{{ $page->title }}</h2>
                            @endif
                            @if($page->subtitle)
                                <div class="text-gray-500 mb-4">{{ $page->subtitle }}</div>
                            @endif
                            <hr class="mb-6 border-gray-300">
                            @foreach($page->questions->sortBy('order') as $question)
                                <div class="mb-8">
                                    <label class="block font-medium mb-2 text-lg">
                                        {{ $questionNumber++ }}. {{ $question->question_text }}
                                        @if($question->required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    @if($question->question_type === 'multiple_choice')
                                        <div class="space-y-2">
                                            @foreach($question->choices as $choice)
                                                <label class="flex items-center space-x-2 shadow-lg p-4">
                                                    <input
                                                        type="checkbox"
                                                        wire:model="answers.{{ $question->id }}.{{ $choice->id }}"
                                                        class="accent-blue-500"
                                                        wire:key="checkbox-{{ $question->id }}-{{ $choice->id }}"
                                                    >
                                                    <span>{{ $choice->choice_text }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === 'radio')
                                        <div class="space-y-2">
                                            @foreach($question->choices as $choice)
                                                <label class="flex items-center space-x-2">
                                                    <input 
                                                        type="radio"
                                                        wire:model="answers.{{ $question->id }}"
                                                        value="{{ $choice->id }}"
                                                        class="accent-blue-500"
                                                    >
                                                    <span>{{ $choice->choice_text }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($question->question_type === 'essay')
                                        <textarea 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border rounded px-3 py-2 w-full focus:border-blue-500"
                                            rows="4"
                                        ></textarea>
                                    @elseif($question->question_type === 'short_text')
                                        <input 
                                            type="text" 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border rounded px-3 py-2 w-full focus:border-blue-500"
                                        >
                                    @elseif($question->question_type === 'date')
                                        <input 
                                            type="date" 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border rounded px-3 py-2 w-full focus:border-blue-500"
                                        >
                                    @elseif($question->question_type === 'rating')
                                        <input 
                                            type="number" min="1" max="5"
                                            wire:model="answers.{{ $question->id }}"
                                            class="border rounded px-3 py-2 w-24 focus:border-blue-500"
                                        >
                                    @else
                                        <input 
                                            type="text" 
                                            wire:model="answers.{{ $question->id }}"
                                            class="border rounded px-3 py-2 w-full focus:border-blue-500"
                                        >
                                    @endif
                                </div>
                            @endforeach

                            <div class="flex justify-between mt-8">
                                <template x-if="currentPage > 0">
                                    <button
                                        type="button"
                                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
                                        @click="currentPage--; window.scrollTo({top: 0, behavior: 'smooth'});"
                                    >Previous</button>
                                </template>

                                @if ($loop->last)
                                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition ml-auto">Submit</button>
                                @else
                                    <button
                                        type="button"
                                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition ml-auto"
                                        x-show="currentPage < {{ count($survey->pages) - 1 }}"
                                        @click="currentPage++; window.scrollTo({top: 0, behavior: 'smooth'});"
                                    >
                                        Next
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>
    </div>
</div>
