@extends('layouts.admin')

@section('page-title', 'Demande de compte')
@section('page-subtitle', $accountRequest->user->name)

@section('content')
<div class="max-w-2xl">
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
    <div class="flex items-center gap-4 mb-6">
      <img src="{{ $accountRequest->user->avatar_url }}" class="w-14 h-14 rounded-full object-cover">
      <div>
        <p class="font-bold text-slate-800 text-lg">{{ $accountRequest->user->name }}</p>
        <p class="text-slate-400 text-sm">{{ $accountRequest->user->email }} · {{ $accountRequest->user->phone }}</p>
      </div>
      <span class="ml-auto badge-draft">{{ ['author'=>'Auteur','auditor'=>'Auditeur','institution'=>'Institution'][$accountRequest->type] ?? $accountRequest->type }}</span>
    </div>

    @if($accountRequest->type === 'institution')
    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
      <div><p class="text-slate-400">Institution</p><p class="font-medium">{{ $accountRequest->institution_name }}</p></div>
      <div><p class="text-slate-400">Pays</p><p class="font-medium">{{ $accountRequest->institution_country }}</p></div>
    </div>
    @endif

    <div class="mb-4">
      <p class="text-slate-400 text-sm mb-1">Motivation</p>
      <p class="text-slate-700 bg-slate-50 rounded-lg p-4">{{ $accountRequest->motivation }}</p>
    </div>

    @if($accountRequest->document_path)
    <div class="mb-4">
      <p class="text-slate-400 text-sm mb-1">Document justificatif</p>
      <a href="{{ asset('storage/' . $accountRequest->document_path) }}" target="_blank" class="text-blue-600 hover:underline text-sm">
        <i class="fa-solid fa-file-arrow-down"></i> Télécharger le document
      </a>
    </div>
    @endif

    <div class="text-sm text-slate-400">
      Soumise le {{ $accountRequest->created_at->format('d/m/Y à H:i') }}
    </div>

    @if($accountRequest->status !== 'pending')
      <div class="mt-4 p-4 rounded-lg {{ $accountRequest->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
        <p class="font-medium">{{ $accountRequest->status === 'approved' ? 'Approuvée' : 'Rejetée' }} par {{ $accountRequest->reviewer->name ?? '—' }}</p>
        @if($accountRequest->admin_note)
          <p class="text-sm mt-1">{{ $accountRequest->admin_note }}</p>
        @endif
      </div>
    @endif
  </div>

  @if($accountRequest->status === 'pending')
  <div class="grid grid-cols-2 gap-4">
    <form method="POST" action="{{ route('admin.accounts.approve', $accountRequest) }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      @csrf
      <p class="font-semibold text-green-700 mb-2"><i class="fa-solid fa-check-circle"></i> Approuver</p>
      <textarea name="note" rows="2" class="w-full text-sm border border-slate-200 rounded-lg p-2 mb-3" placeholder="Note (optionnelle)"></textarea>
      <button type="submit" class="w-full py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700">Activer le compte</button>
    </form>

    <form method="POST" action="{{ route('admin.accounts.reject', $accountRequest) }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      @csrf
      <p class="font-semibold text-red-700 mb-2"><i class="fa-solid fa-circle-xmark"></i> Rejeter</p>
      <textarea name="note" rows="2" required class="w-full text-sm border border-slate-200 rounded-lg p-2 mb-3" placeholder="Raison du rejet (obligatoire)"></textarea>
      <button type="submit" class="w-full py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">Rejeter la demande</button>
    </form>
  </div>
  @endif
</div>
@endsection
