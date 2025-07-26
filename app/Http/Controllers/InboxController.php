<?php

namespace App\Http\Controllers;

use App\Models\InboxMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InboxController extends Controller
{
    /**
     * Display a listing of inbox messages.
     */
    public function index()
    {
        return view('inbox.index');
    }

    /**
     * Display the specified inbox message.
     */
    public function show($id)
    {
        $message = InboxMessage::where('id', $id)->first();
        
        if (!$message) {
            abort(404, 'The requested page could not be found.');
        }
        
        // Check if user owns this message
        if ($message->recipient_id !== Auth::id()) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        // Mark as read if not already
        if (!$message->read_at) {
            $message->read_at = now();
            $message->save();
        }
        
        return view('inbox.show', compact('message'));
    }

    /**
     * Send a test message (for testing purposes).
     */
    public function sendTestMessage(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        InboxMessage::create([
            'recipient_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'url' => $request->url,
        ]);
        
        return redirect()->back()->with('success', 'Test message sent to your inbox!');
    }
}
