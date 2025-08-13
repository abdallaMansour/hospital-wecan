<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FCMService; // Import the FCMService
use App\Models\User; // Assuming User model has the fcm_token field

class ChatController extends Controller
{
    // protected $fcmService;

    // public function __construct(FCMService $fcmService)
    // {
    //     $this->fcmService = $fcmService;
    // }

    public function createRoom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        $chatRoom = ChatRoom::create($validated);

        return response()->json($chatRoom, 201);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'message_type' => 'required|in:text,media',
            'message' => 'nullable|required_if:message_type,text|string',
            'attachment_path' => 'nullable|required_if:message_type,media|file',
        ]);

        $user = Auth::user();

        if (ChatRoom::find($validated['chat_room_id'])->patient_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to send message in this chat room',
            ], 403);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment_path')) {
            $attachmentPath = $request->file('attachment_path')->store('attachments', 'public');
        }

        $message = ChatMessage::create([
            'chat_room_id' => $validated['chat_room_id'],
            'user_id' => $user->id,
            'message' => $validated['message'],
            'message_type' => $validated['message_type'],
            'attachment_path' => $attachmentPath,
        ]);

        // Fetch the users in the chat room
        $chatRoom = ChatRoom::with(['doctor', 'patient', 'hospital'])->find($validated['chat_room_id']);
        $recipients = [$chatRoom->doctor, $chatRoom->patient];

        if ($chatRoom->hospital_id) {
            $recipients[] = $chatRoom->hospital;
        }

        // try {
        //     // Send FCM notification to each recipient
        //     foreach ($recipients as $recipient) {
        //         if ($recipient->fcm_token && $recipient->id !== $user->id) {
        //             $response = $this->fcmService->sendNotification(
        //                 $recipient->fcm_token,
        //                 'New Message from ' . $user->name,
        //                 $validated['message']

        //             );
        //             if (isset($response['error'])) {
        //                 \Log::error('FCM Notification Error', [
        //                     'recipient_id' => $recipient->id,
        //                     'error' => $response['error'],
        //                 ]);
        //             } else {
        //                 \Log::info('FCM Notification Sent', [
        //                     'recipient_id' => $recipient->id,
        //                     'message_id' => $response['name'],
        //                 ]);
        //             }
        //         }
        //     }
        // } catch (\Exception $e) {
        //     \Log::error('FCM Notification Exception', [
        //         'exception' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString(),
        //     ]);
        // }

        return response()->json([
            'message' => $message,
            'user' => $user->only(['id', 'name']),
        ], 201);
    }

    public function getRoomMessages(Request $request)
    {
        $validated = $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $user = Auth::user();

        $chatRoom = ChatRoom::find($validated['chat_room_id']);

        if ($chatRoom->patient_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to view this chat room',
            ], 403);
        }

        $perPage = $validated['per_page'] ?? 50;
        $page = $validated['page'] ?? 1;

        $messages = ChatMessage::where('chat_room_id', $validated['chat_room_id'])
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        // update the is_read status of the messages
        ChatMessage::where('chat_room_id', $validated['chat_room_id'])
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function getUserRooms(Request $request)
    {
        $user = Auth::user();

        $query = ChatRoom::query();

        if ($user->account_type === 'doctor') {
            $query->where('doctor_id', $user->id);
        } elseif ($user->account_type === 'patient') {
            $query->where('patient_id', $user->id);
        } elseif ($user->account_type === 'hospital') {
            $query->where('hospital_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('doctor_id', $user->id)
                    ->orWhere('patient_id', $user->id)
                    ->orWhere('hospital_id', $user->id);
            });
        }

        $rooms = $query->with(['doctor', 'patient', 'hospital'])->get();

        return response()->json($rooms);
    }
}
