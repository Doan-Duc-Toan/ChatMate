<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use App\Models\Conversation;

class Users extends Component
{
    public function message($userId){
        $authenticatedUserId = auth()->id();
        $existingConversation = Conversation::where(function ($query) use ($authenticatedUserId, $userId){
               $query->where('sender_id', $authenticatedUserId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authenticatedUserId, $userId){
            $query->where('receiver_id', $authenticatedUserId)->where('sender_id', $userId);
        })->first();
        if($existingConversation){
            return redirect()->route('chat',['query'=>$existingConversation->id]);
        }




        // -------createConverastion--------
        $createdConversation = Conversation::create([
              'sender_id' => $authenticatedUserId,
              'receiver_id' => $userId
        ]);
        return redirect()->route('chat',['query'=>$createdConversation->id]);

    }
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.users', ['users' => User::where('id','!=',auth()->id())->get()]);
    }
}
