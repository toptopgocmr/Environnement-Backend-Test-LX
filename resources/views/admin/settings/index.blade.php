@extends('layouts.admin')
@section('title', 'Paramètres')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Paramètres de la plateforme</h1>
        <p class="text-slate-400 text-sm mt-1">Configuration globale de LireX</p>
    </div>

    @if(session('success'))
        <div class="bg-green-900/30 border border-green-700 text-green-300 rounded-xl p-4 text-sm">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Plateforme générale --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">⚙️ Général</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Nom de la plateforme</label>
                    <input type="text" name="platform_name" value="{{ $settings['platform_name'] ?? 'LireX' }}"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Email de contact</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? 'contact@lirex.africa' }}"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div class="col-span-2">
                    <label class="block text-slate-300 text-sm font-medium mb-2">Description / Slogan</label>
                    <input type="text" name="platform_tagline" value="{{ $settings['platform_tagline'] ?? 'Plateforme Universelle du Savoir' }}"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
            </div>
        </div>

        {{-- Royalties --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">💰 Royalties & Commissions</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Commission plateforme (%)</label>
                    <input type="number" name="platform_commission" value="{{ $settings['platform_commission'] ?? 20 }}" min="0" max="50" step="0.5"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Royaltie auteur (%)</label>
                    <input type="number" name="author_royalty" value="{{ $settings['author_royalty'] ?? 80 }}" min="50" max="100" step="0.5"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Retrait minimum (FCFA)</label>
                    <input type="number" name="min_withdrawal" value="{{ $settings['min_withdrawal'] ?? 5000 }}" step="500"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
            </div>
        </div>

        {{-- Paiements --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">📱 Moyens de paiement</h2>
            <div class="space-y-3">
                @foreach([['peex', '📱 Mobile Money (Peex)'], ['stripe', '💳 Stripe (carte)'], ['free', '🆓 Livres gratuits']] as [$key, $label])
                    <label class="flex items-center gap-3 p-4 rounded-xl cursor-pointer" style="background:#0F2044;border:1px solid #1E3A6A">
                        <input type="checkbox" name="payment_methods[]" value="{{ $key }}"
                            {{ in_array($key, $settings['payment_methods'] ?? ['peex','free']) ? 'checked' : '' }}
                            class="w-4 h-4 rounded">
                        <span class="text-white text-sm">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Modération --}}
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">🛡️ Modération</h2>
            <div class="space-y-3">
                <label class="flex items-center justify-between p-4 rounded-xl" style="background:#0F2044;border:1px solid #1E3A6A">
                    <span class="text-white text-sm">Approbation manuelle des livres</span>
                    <div class="relative">
                        <input type="hidden" name="manual_book_approval" value="0">
                        <input type="checkbox" name="manual_book_approval" value="1" {{ ($settings['manual_book_approval'] ?? true) ? 'checked' : '' }}
                            class="sr-only peer" id="toggle-approval">
                        <label for="toggle-approval" class="w-11 h-6 rounded-full cursor-pointer transition-colors peer-checked:bg-blue-600"
                            style="background:#1E3A6A;display:block;position:relative">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 left-1 transition-transform peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </label>
                <label class="flex items-center justify-between p-4 rounded-xl" style="background:#0F2044;border:1px solid #1E3A6A">
                    <span class="text-white text-sm">Notifications SMS activées</span>
                    <div class="relative">
                        <input type="hidden" name="sms_enabled" value="0">
                        <input type="checkbox" name="sms_enabled" value="1" {{ ($settings['sms_enabled'] ?? true) ? 'checked' : '' }}
                            class="sr-only peer" id="toggle-sms">
                        <label for="toggle-sms" class="w-11 h-6 rounded-full cursor-pointer transition-colors peer-checked:bg-blue-600"
                            style="background:#1E3A6A;display:block;position:relative">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 left-1 transition-transform peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl font-semibold text-white text-sm hover:opacity-90 transition-all"
                style="background:linear-gradient(135deg,#1D4ED8,#2563EB)">
                Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>
@endsection
