<?php

namespace App\Notifications;

use App\Models\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class NewChatMessageNotification extends Notification
{
    use Queueable;

    protected $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function via($notifiable)
    {
        return ['database']; // simpan di database, bisa tambahkan 'mail' juga
    }

    public function toDatabase($notifiable)
    {
        return [
            'chat_id' => $this->chat->id,
            'sender_id' => $this->chat->sender_id,
            'message' => $this->chat->message,
            'sellaccount_id' => $this->chat->sellaccount_id,
            'created_at' => $this->chat->created_at,
        ];
    }
}
