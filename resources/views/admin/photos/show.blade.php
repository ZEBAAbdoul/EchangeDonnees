<x-admin>

    @section('title', 'Détail de la photo')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-image mr-2" aria-hidden="true"></i> Détail de la photo
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.photos.edit', $photo) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit mr-1" aria-hidden="true"></i> Modifier
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">

                {{-- Image --}}
                <div class="col-md-7 text-center mb-3">
                    @if ($photo->image_src)
                        <button type="button" id="ouvrir-photo" class="btn btn-link p-0"
                                aria-label="Agrandir la photo « {{ $photo->titre }} »">
                            <img src="{{ $photo->image_src }}" alt="{{ $photo->titre }}"
                                 class="img-fluid img-thumbnail" style="max-height:500px;"
                                 onerror="this.onerror=null;this.style.opacity=.25;this.title='Image introuvable : {{ e($photo->image_url) }}';">
                        </button>
                    @else
                        <div class="text-muted py-5">
                            <i class="fas fa-image fa-4x" aria-hidden="true"></i>
                            <p class="mt-2">Aucune image disponible.</p>
                        </div>
                    @endif
                </div>

                {{-- Informations --}}
                <div class="col-md-5">
                    <h4 class="mb-3">{{ $photo->titre }}</h4>

                    <div class="mb-3">
                        <strong><i class="fas fa-align-left mr-1" aria-hidden="true"></i> Description</strong>
                        <div class="text-muted mt-1">
                            @if ($photo->description)
                                {!! nl2br(e($photo->description)) !!}
                            @else
                                <em>Aucune description.</em>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <strong><i class="fas fa-sort-numeric-down mr-1" aria-hidden="true"></i> Ordre</strong>
                        <span class="ml-2">{{ $photo->ordre }}</span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-eye mr-1" aria-hidden="true"></i> Visibilité</strong>
                        <span class="ml-2">
                            @if ($photo->est_visible)
                                <span class="badge badge-success"><i class="fas fa-check mr-1" aria-hidden="true"></i> Visible</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times mr-1" aria-hidden="true"></i> Masquée</span>
                            @endif
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-plus mr-1" aria-hidden="true"></i> Créée le</strong>
                        <span class="ml-2">{{ $photo->created_at?->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-calendar-check mr-1" aria-hidden="true"></i> Modifiée le</strong>
                        <span class="ml-2">{{ $photo->updated_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('admin.photos.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Retour à la liste
            </a>
        </div>
    </div>

    {{-- Visionneuse --}}
    @if ($photo->image_src)
        <div id="lightbox" role="dialog" aria-modal="true" aria-label="Photo agrandie" hidden
             style="position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:9999;align-items:center;justify-content:center;">
            <button type="button" id="lb-fermer" aria-label="Fermer"
                    style="position:absolute;top:20px;right:20px;background:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:24px;cursor:pointer;">&times;</button>
            <figure style="text-align:center;max-width:90%;margin:0;">
                <h3 id="lb-titre" style="color:#fff;margin-bottom:10px;"></h3>
                <img id="lb-image" alt="" style="max-width:100%;max-height:80vh;object-fit:contain;">
            </figure>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const photo = @json(['url' => $photo->image_src, 'titre' => $photo->titre]);
            const box = document.getElementById('lightbox');
            const ouvrir = document.getElementById('ouvrir-photo');

            function fermer() {
                box.hidden = true;
                box.style.display = 'none';
                document.body.style.overflow = '';
                ouvrir.focus();
            }

            ouvrir.addEventListener('click', function () {
                document.getElementById('lb-titre').textContent = photo.titre;
                const img = document.getElementById('lb-image');
                img.src = photo.url;
                img.alt = photo.titre;
                box.hidden = false;
                box.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                document.getElementById('lb-fermer').focus();
            });

            document.getElementById('lb-fermer').addEventListener('click', fermer);
            box.addEventListener('click', function (e) { if (e.target === box) fermer(); });
            document.addEventListener('keydown', function (e) {
                if (!box.hidden && e.key === 'Escape') fermer();
            });
        });
        </script>
    @endif

</x-admin>