@extends('layouts.site')

@section('title', 'Recherche — DGTI')

@section('content')
    <section class="dg-wrap" style="padding:48px 0 64px">
        <h1 style="font-size:clamp(26px,3vw,36px);margin-bottom:8px">Résultats de recherche</h1>

        @if (mb_strlen($q) < 2)
            <p style="color:var(--dg-muted)">Saisissez au moins 2 caractères.</p>
        @else
            <p style="color:var(--dg-muted);margin-bottom:28px">
                {{ $resultats->count() }} résultat(s) pour « {{ $q }} »
            </p>

            <ul style="display:grid;gap:14px">
                @forelse ($resultats as $r)
                    <li style="border:1px solid var(--dg-line);border-radius:8px;padding:16px 20px">
                        <small
                            style="color:var(--dg-accent);font-weight:700;text-transform:uppercase">{{ $r['type'] }}</small>
                        <h2 style="font-size:20px;margin:4px 0">
                            <a href="{{ $r['url'] }}" style="text-decoration:none">{{ $r['titre'] }}</a>
                        </h2>
                        @if ($r['extrait'])
                            <p style="color:var(--dg-muted)">{{ $r['extrait'] }}</p>
                        @endif
                    </li>
                @empty
                    <li>Aucun résultat trouvé.</li>
                @endforelse
            </ul>
        @endif
    </section>
@endsection
