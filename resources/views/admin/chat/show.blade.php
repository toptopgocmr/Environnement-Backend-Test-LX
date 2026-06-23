@extends('layouts.admin')

@section('page-title', 'Conversation')
@section('page-subtitle', $conversation->subject ?? '')

@section('content')
<div class="max-w-2xl mx-auto">

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-4">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
      <div>
        @foreach($conversation->participants as $p)
          @if($p->user_id !== auth()->id())
            <p class="font-semibold text-slate-800">{{ $p->user->name }}</p>
            <p class="text-slate-400 text-xs">{{ $p->user->email }} · {{ ucfirst($p->user->role) }}</p>
          @endif
        @endforeach
      </div>
      @if($conversation->status === 'open')
      <form method="POST" action="{{ route('admin.chat.close', $conversation) }}">
        @csrf
        <button type="submit" class="text-sm text-red-600 hover:underline">Fermer la conversation</button>
      </form>
      @else
        <span class="badge-rejected">Fermée</span>
      @endif
    </div>

    <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto">
      @foreach($messages as $msg)
        @php $isAdmin = $msg->sender_id === auth()->id(); @endphp
        <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
          <div class="max-w-xs">
            @if($msg->type === 'system')
              <p class="text-center text-xs text-slate-400 italic">{{ $msg->body }}</p>
            @else
              <div class="rounded-2xl px-4 py-2.5 text-sm {{ $isAdmin ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-slate-100 text-slate-800 rounded-bl-sm' }}">
                {{ $msg->body }}
              </div>
              <p class="text-xs text-slate-400 mt-1 {{ $isAdmin ? 'text-right' : 'text-left' }}">{{ $msg->created_at->format('H:i') }}</p>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    @if($conversation->status === 'open')
    <form method="POST" action="{{ route('admin.chat.message', $conversation) }}" class="p-4 border-t border-slate-100 flex gap-2">
      @csrf
      <input type="text" name="body" required class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="Votre message...">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Envoyer</button>
    </form>
    @endif
  </div>

  <a href="{{ route('admin.chat.index') }}" class="text-blue-600 text-sm hover:underline">← Retour aux conversations</a>
</div>
@endsection
