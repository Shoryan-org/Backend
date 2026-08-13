<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $notification,
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }
}