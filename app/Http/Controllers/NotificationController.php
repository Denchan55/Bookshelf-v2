<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
{
    $notifications = Notification::where('user_id', auth()->id())
        ->latest()
        ->get();

    return view('notifications.index', compact('notifications'));
}

}
