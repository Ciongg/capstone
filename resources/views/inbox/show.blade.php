@extends('components.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-800">{{ $message->subject }}</h1>
            <a href="{{ route('inbox.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Back to Inbox
            </a>
        </div>
        
        <div class="px-6 py-4">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 mr-4">
                    <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white">
                        {{ substr($message->sender->name ?? 'U', 0, 1) }}
                    </div>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $message->sender->name ?? 'Unknown Sender' }}</p>
                    <p class="text-sm text-gray-500">{{ $message->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
            
            <div class="prose prose-sm sm:prose max-w-none">
                {{ $message->message }}
            </div>
            
            @if ($message->url)
                <div class="mt-6 border-t pt-4">
                    <a href="{{ $message->url }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        View Related Content
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
