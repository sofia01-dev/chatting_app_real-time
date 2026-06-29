<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('is_online', 'desc')
            ->orderBy('name')
            ->get();

        foreach ($users as $user) {

            $user->lastMessage = Message::where(function ($q) use ($user) {
                $q->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
            })
            ->latest()
            ->first();
        }

        return view('chat.index', ['users' => $users]);
    }

    public function show($id)
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('is_online', 'desc')
            ->orderBy('name')
            ->get();

        foreach ($users as $user) {

            $user->lastMessage = Message::where(function ($q) use ($user) {
                $q->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
            })
            ->latest()
            ->first();
        }

        $selectedUser = User::findOrFail($id);

        $messages = Message::where(function ($q) use ($id) {
                $q->where('sender_id', Auth::id())->where('receiver_id', $id);
            })
            ->orWhere(function ($q) use ($id) {
                $q->where('sender_id', $id)->where('receiver_id', Auth::id());
            })
            ->orderBy('created_at')
            ->get();

        return view('chat.index', [
            'users'        => $users,
            'selectedUser' => $selectedUser,
            'messages'     => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        // Validasi input
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'message'     => 'nullable|string|max:5000',
            'file'        => 'nullable|file|max:10240',
        ]);

        $messageType = 'text';
        $filePath    = null;
        $fileName    = null;
        $fileSize    = null;

        // Proses upload file jika ada
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $filePath = $file->store('chat-files', 'public');

            // Tentukan tipe pesan berdasarkan mime type
            $mime = $file->getMimeType();
            if (str_starts_with($mime, 'image/')) {
                $messageType = 'image';
            } else {
                $messageType = 'file';
            }
        }

        // Wajib ada salah satu: pesan teks atau file
        if (!$request->message && !$filePath) {
            return response()->json(['error' => 'Pesan atau file wajib diisi.'], 422);
        }

        $message = Message::create([
            'sender_id'    => Auth::id(),
            'receiver_id'  => $request->receiver_id,
            'message'      => $request->message ?? '',
            'message_type' => $messageType,
            'file_path'    => $filePath,
            'file_name'    => $fileName,
            'file_size'    => $fileSize,
            'is_read'      => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success'      => true,
            'id'           => $message->id,
            'sender_id'    => $message->sender_id,
            'receiver_id'  => $message->receiver_id,
            'message'      => $message->message,
            'message_type' => $message->message_type,
            'file_path'    => $message->file_path ? asset('storage/' . $message->file_path) : null,
            'file_name'    => $message->file_name,
            'file_size'    => $message->file_size,
            'created_at'   => $message->created_at->toISOString(),
        ]);
    }
}
