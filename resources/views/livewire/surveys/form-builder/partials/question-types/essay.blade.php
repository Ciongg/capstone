<textarea
    class="w-full border rounded px-3 py-2 mt-2 resize-none cursor-pointer"
    rows="4"
    readonly
    tabindex="-1"
    placeholder="Essay response (multi-line, wraps)"
    @click="selectedQuestionId = {{ $question->id }}; activePageId = {{ $question->survey_page_id }}; $wire.selectQuestion({{ $question->id }})"
></textarea>
