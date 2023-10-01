<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Chat extends Component
{
    public $query;
    public $selectedConversation;
    public function mount()
    {
        $this->selectedConversation = Conversation::findOrFail($this->query);
        // dd($this->selectedConversation); 

        Message::where('conversation_id', $this->selectedConversation->id)->where('receiver_id', auth()->id())->whereNull('read_at')->update(['read_at'=> now()]);
    }
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.chat', [
            'query' => $this->query,
            'selectedConversation' => $this->selectedConversation
        ]);
    }
}
