<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Получить уведомления текущего пользователя (JSON для bell icon)
     */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->with('vacancyRequest.position')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $notifications->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Пометить одно уведомление как прочитанное
     */
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Пометить все уведомления как прочитанные
     */
    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}