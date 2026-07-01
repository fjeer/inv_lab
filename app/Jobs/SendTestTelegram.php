<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\TestTelegramNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class SendTestTelegram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $users = User::whereNotNull('telegram_chat_id')->get();

        foreach ($users as $user) {
            $user->notify(new TestTelegramNotification);
        }
    }
}
