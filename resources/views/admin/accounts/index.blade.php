@extends('layouts.admin')

@section('page-title', 'Demandes de compte')
@section('page-subtitle', 'Activation des comptes auteur, auditeur et institution')

@section('content')
<div class="flex gap-3 mb-6">
  @php $current = request('status'); @endphp
  <a href="{{ route('admin.accounts.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ !$current ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
    Toutes
  </a>
  <a href="{{ route('admin.accounts.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $current === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
    En attente <span class="ml-1">({{ $counts['pending'] }})</span>
  </a>
  <a href="{{ route('admin.accounts.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $current === 'approved' ? 'bg-green-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
    Approuvées <span class="ml-1">({{ $counts['approved'] }})</span>
  </a>
  <a href="{{ route('admin.accounts.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-lg text-sm font-medium {{ $current === 'rejected' ? 'bg-red-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
    Rejetées <span class="ml-1">({{ $counts['rejected'] }})</span>
  </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Demandeur</th>
        <th class="px-6 py-3 text-left">Type</th>
        <th class="px-6 py-3 text-left">Motivation</th>
        <th class="px-6 py-3 text-left">Statut</th>
        <th class="px-6 py-3 text-left">Date</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @forelse($requests as $req)
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4">
          <p class="font-medium text-slate-800">{{ $req->user->name }}</p>
          <p class="text-slate-400 text-xs">{{ $req->user->email }}</p>
        </td>
        <td class="px-6 py-4">
          <span class="badge-draft">{{ ['author'=>'Auteur','auditor'=>'Auditeur','institution'=>'Institution'][$req->type] ?? $req->type }}</span>
        </td>
        <td class="px-6 py-4 max-w-xs truncate text-slate-600">{{ $req->motivation }}</td>
        <td class="px-6 py-4">
          @if($req->status === 'pending') <span class="badge-pending">En attente</span>
          @elseif($req->status === 'approved') <span class="badge-published">Approuvée</span>
          @else <span class="badge-rejected">Rejetée</span>
          @endif
        </td>
        <td class="px-6 py-4 text-slate-400">{{ $req->created_at->format('d/m/Y') }}</td>
        <td class="px-6 py-4 text-right">
          <a href="{{ route('admin.accounts.show', $req) }}" class="text-blue-600 hover:underline font-medium">Examiner</a>
        </td>
      </tr>
      @empty
      <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">Aucune demande.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-6">{{ $requests->links() }}</div>
@endsection
