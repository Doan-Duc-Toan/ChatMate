<?php

namespace App\Livewire;

use App\Models\Message;
use App\Notifications\MessageRead;
use App\Notifications\MessageSent;
use Livewire\Component;
use Livewire\Attributes\On;
use function Laravel\Prompts\select;

class ChatBox extends Component
{
    public $body;
    public $selectedConversation;
    public $loadedMessages;
    public $paginate_var = 10;
    // protected $listeners = [
    //     'loadMore'
    // ];
    // #[On('loadMore')] 
    public function getListeners()
    {

        $auth_id = auth()->id();
        return [

            'loadMore',
            "echo-private:users.{$auth_id},.sendMessage" => 'broadcastedNotifications'

        ];
    }

    public function broadcastedNotifications($event)
    {
        // dd($event);
        if ($event['conversation_id'] == $this->selectedConversation->id) {
            $this->dispatch('scroll-bottom');
            $newMessage = Message::find($event['message_id']);
            $this->loadedMessages->push($newMessage);

            $newMessage->read_at = now();
            $newMessage->save();
            $this->selectedConversation->getReceiver()
            ->notify(new MessageRead($this->selectedConversation->id));

        }
    }


    #[On('loadMore')]

    public function loadMore(): void
    {
        $this->paginate_var += 10;
        $this->loadMessages();

        #update the chat height
        $this->dispatch('update-chat-height');
    }
    public function sendMessage()
    {
        $this->validate(['body' => 'required|string']);
        $createdMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedConversation->getReceiver()->id,
            'body' => $this->body,
        ]);
        $this->reset('body');
        $this->dispatch('scroll-bottom');
        #push the message
        $this->loadedMessages->push($createdMessage);


        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();

        $this->dispatch('refresh');

        $this->selectedConversation->getReceiver()
            ->notify(new MessageSent(
                auth()->user(),
                $createdMessage,
                $this->selectedConversation,
                $this->selectedConversation->getReceiver()->id
            ));
    }
    public function loadMessages()
    {
        $count = Message::where('conversation_id', $this->selectedConversation->id)->count();

        $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
            ->skip($count - $this->paginate_var)
            ->take($this->paginate_var)
            ->get();
        return $this->loadedMessages;
    }
    public function mount()
    {
        $this->loadMessages();
    }
    public function render()
    {
        return view('livewire.chat-box');
    }
}
