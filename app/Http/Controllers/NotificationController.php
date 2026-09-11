<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index()
{
    //dd(auth()->user()->notifications());

    $notifications = auth()->user()
        ->notifications()
        ->latest()
        ->get();

    return view('notifications.index', compact('notifications'));
}

public function read($id)
{
    $notification = auth()->user()->notifications()->findOrFail($id);

    // 既読化
    $notification->markAsRead();

    return redirect()->back();
}

}
