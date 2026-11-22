<x-admin>

    @section('title', 'Détail de la vidéo')

    @php
        $lien = $video->safe_url;          // null si le lien n'est pas en http(s)
        $embed = $video->embed_url;
        $miniature = $video->thumbnail_url;
    @endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-video mr-2" aria-hidden="true"></i> Détail de la vidéo
                <span class="badge badge-info ml-2">{{ $video->platform }}</span>
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit mr-1" aria-hidden="true"></i> Modifier
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">

                {{-- Lecteur --}}
                <div class="col-md-8 mb-3">
                    <div class="embed-responsive embed-responsive-16by9 bg-dark rounded">
                        @if ($embed)
                            <iframe class="embed-responsive-item" src="{{ $embed }}" title="{{ $video->titre }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        @elseif ($video->is_file && $lien)
                            <video class="embed-responsive-item" src="{{ $lien }}" controls preload="metadata"
                                   @if ($miniature) poster="{{ $miniature }}" @endif></video>
                        @else
                            <div class="embed-responsive-item d-flex align-items-center justify-content-center text-center text-muted p-3"
                                 @if ($miniature) style="background:url('{{ $miniature }}') center/cover;" @endif>
                                <div>
                                    <i class="fas fa-video fa-2x d-block mb-2" aria-hidden="true"></i>
                                    <span class="small">
                                        Aperçu non disponible pour cette plateforme. Ouvrez la vidéo.
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3">
                        @if ($lien)
                            <a href="{{ $lien }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                                <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i>
                                Ouvrir sur {{ $video->platform === 'Vidéo' ? 'le site d’origine' : $video->platform }}
                            </a>
                            @if ($embed)
                                <small class="text-muted ml-2">
                                    La vidéo ne s’affiche pas ? Certaines plateformes refusent l’intégration.
                                </small>
                            @endif
                        @else
                            <span class="text-danger small">
                                <i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i>
                                Le lien enregistré n’est pas une adresse web valide (http ou https).
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Informations --}}
                <div class="col-md-4">
                    <h4>{{ $video->titre }}</h4>
                    <hr>

                    <div class="mb-3">
                        <strong><i class="fas fa-align-left mr-1" aria-hidden="true"></i> Description</strong>
                        <div class="text-muted mt-1">
                            @if ($video->description)
                                {!! nl2br(e($video->description)) !!}
                            @else
                                <em>Aucune description.</em>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-link mr-1" aria-hidden="true"></i> Lien</strong>
                        <div class="mt-1 text-break" style="word-break:break-all;">
                            @if ($lien)
                                <a href="{{ $lien }}" target="_blank" rel="noopener noreferrer">{{ $video->url }}</a>
                            @else
                                {{ $video->url }}
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-sort-numeric-down mr-1" aria-hidden="true"></i> Ordre</strong>
                        <span class="ml-2">{{ $video->ordre }}</span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-eye mr-1" aria-hidden="true"></i> Visibilité</strong>
                        <span class="ml-2">
                            @if ($video->est_visible)
                                <span class="badge badge-success"><i class="fas fa-check mr-1" aria-hidden="true"></i> Visible</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times mr-1" aria-hidden="true"></i> Masquée</span>
                            @endif
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-plus mr-1" aria-hidden="true"></i> Créée le</strong>
                        <span class="ml-2">{{ $video->created_at?->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-check mr-1" aria-hidden="true"></i> Modifiée le</strong>
                        <span class="ml-2">{{ $video->updated_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Retour à la liste
            </a>
        </div>
    </div>

</x-admin>