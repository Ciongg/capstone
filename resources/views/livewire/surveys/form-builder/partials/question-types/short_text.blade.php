<input
    type="text"
    class="w-full border rounded px-3 py-2 mt-2 cursor-pointer"
    readonly
    tabindex="-1"
    placeholder="Short text response (single line, no wrap)"
    @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $question->survey_page_id }}; $wire.selectQuestion({{ $question->id }})"
>
