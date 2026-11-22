<x-admin>

    @section('title', 'Photos')

    @php
        $estPagine = $photos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
        $items = $estPagine ? $photos->getCollection() : collect($photos);

        // Seules les photos avec image alimentent la visionneuse.
        $galerie = $items->filter(fn ($p) => $p->image_src)->values();
        $positions = $galerie->pluck('id')->flip();
        $galerieJs = $galerie->map(fn ($p) => [
            'titre' => $p->titre,
            'description' => $p->description,
            'url' => $p->image_src,
        ])->all();
    @endphp

    @foreach (['status' => 'info', 'success' => 'success'] as $cle => $type)
        @if (session($cle))
            <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                {{ session($cle) }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    @endforeach

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-images mr-2" aria-hidden="true"></i> Photos
                @if ($estPagine)
                    <span class="badge badge-light ml-2">{{ $photos->total() }}</span>
                @endif
            </h3>
            <div class="card-tools">
                <a href="{{ route('admin.photos.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus" aria-hidden="true"></i> Nouvelle photo
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th style="width:130px;">Image</th>
                            <th>Titre</th>
                            <th style="width:100px;">Ordre</th>
                            <th style="width:120px;">Visibilité</th>
                            <th style="width:150px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $photo)
                            <tr>
                                <td class="text-center align-middle">
                                    {{ $estPagine ? $photos->firstItem() + $loop->index : $loop->iteration }}
                                </td>

                                <td class="text-center align-middle">
                                    @if ($photo->image_src)
                                        <button type="button" class="btn btn-link p-0 js-ouvrir-photo"
                                                data-index="{{ $positions[$photo->id] }}"
                                                aria-label="Agrandir la photo « {{ $photo->titre }} »">
                                            <img src="{{ $photo->image_src }}" alt="{{ $photo->titre }}"
                                                 class="img-thumbnail" loading="lazy"
                                                 onerror="this.onerror=null;this.style.opacity=.25;this.title='Image introuvable : {{ e($photo->image_url) }}';"
                                                 style="width:100px;height:70px;object-fit:cover;">
                                        </button>
                                    @else
                                        <i class="fas fa-image fa-2x text-muted" aria-hidden="true"></i>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    <strong>{{ $photo->titre }}</strong>
                                    @if ($photo->description)
                                        <div class="text-muted small mt-1">
                                            {{ \Illuminate\Support\Str::limit($photo->description, 100) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="text-center align-middle">{{ $photo->ordre }}</td>

                                <td class="text-center align-middle">
                                    @if ($photo->est_visible)
                                        <span class="badge badge-success">
                                            <i class="fas fa-eye mr-1" aria-hidden="true"></i> Visible
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-eye-slash mr-1" aria-hidden="true"></i> Masquée
                                        </span>
                                    @endif
                                </td>

                                <td class="align-middle text-nowrap">
                                    <a href="{{ route('admin.photos.show', $photo) }}" class="btn btn-sm btn-info"
                                       title="Voir" aria-label="Voir {{ $photo->titre }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>

                                    <a href="{{ route('admin.photos.edit', $photo) }}" class="btn btn-sm btn-warning"
                                       title="Modifier" aria-label="Modifier {{ $photo->titre }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>

                                    <form action="{{ route('admin.photos.destroy', $photo) }}" method="POST"
                                          class="d-inline-block delete-photo-form"
                                          data-photo-titre="{{ $photo->titre }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer"
                                                aria-label="Supprimer {{ $photo->titre }}">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-images fa-2x mb-2" aria-hidden="true"></i><br>
                                    Aucune photo trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($estPagine && $photos->hasPages())
            <div class="card-footer">
                {{ $photos->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- Visionneuse --}}
    <div id="lightbox" role="dialog" aria-modal="true" aria-label="Visionneuse de photos" hidden
         style="position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:9999;align-items:center;justify-content:center;">
        <button type="button" id="lb-fermer" aria-label="Fermer"
                style="position:absolute;top:20px;right:20px;background:#fff;border:0;border-radius:50%;width:40px;height:40px;font-size:24px;cursor:pointer;">&times;</button>
        <button type="button" id="lb-prec" aria-label="Photo précédente"
                style="position:absolute;left:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.2);border:0;border-radius:50%;width:50px;height:50px;font-size:24px;color:#fff;cursor:pointer;">&#10094;</button>

        <figure style="text-align:center;max-width:90%;margin:0;">
            <h3 id="lb-titre" style="color:#fff;margin-bottom:10px;"></h3>
            <img id="lb-image" alt="" style="max-width:100%;max-height:75vh;object-fit:contain;">
            <figcaption id="lb-legende" style="color:#ddd;margin-top:10px;"></figcaption>
            <p id="lb-compteur" style="color:#fff;margin:6px 0 0;"></p>
        </figure>

        <button type="button" id="lb-suiv" aria-label="Photo suivante"
                style="position:absolute;right:20px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.2);border:0;border-radius:50%;width:50px;height:50px;font-size:24px;color:#fff;cursor:pointer;">&#10095;</button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ----- Visionneuse -----
        const photos = @json($galerieJs);
        const box = document.getElementById('lightbox');
        const img = document.getElementById('lb-image');
        let courant = 0;
        let dernierFocus = null;

        function afficher(i) {
            if (!photos.length) return;
            courant = (i + photos.length) % photos.length;
            const p = photos[courant];
            img.src = p.url;
            img.alt = p.titre;
            document.getElementById('lb-titre').textContent = p.titre;
            document.getElementById('lb-legende').textContent = p.description || '';
            document.getElementById('lb-compteur').textContent = (courant + 1) + ' / ' + photos.length;
        }

        function ouvrir(i) {
            dernierFocus = document.activeElement;
            afficher(i);
            box.hidden = false;
            box.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            document.getElementById('lb-fermer').focus();
        }

        function fermer() {
            box.hidden = true;
            box.style.display = 'none';
            document.body.style.overflow = '';
            if (dernierFocus) dernierFocus.focus();
        }

        document.querySelectorAll('.js-ouvrir-photo').forEach(function (btn) {
            btn.addEventListener('click', function () { ouvrir(parseInt(btn.dataset.index, 10)); });
        });
        document.getElementById('lb-fermer').addEventListener('click', fermer);
        document.getElementById('lb-prec').addEventListener('click', function () { afficher(courant - 1); });
        document.getElementById('lb-suiv').addEventListener('click', function () { afficher(courant + 1); });
        box.addEventListener('click', function (e) { if (e.target === box) fermer(); });

        document.addEventListener('keydown', function (e) {
            if (box.hidden) return;
            if (e.key === 'Escape') fermer();
            else if (e.key === 'ArrowRight') afficher(courant + 1);
            else if (e.key === 'ArrowLeft') afficher(courant - 1);
        });

        // Balayage tactile
        let departX = null;
        box.addEventListener('touchstart', function (e) { departX = e.changedTouches[0].clientX; }, {passive: true});
        box.addEventListener('touchend', function (e) {
            if (departX === null) return;
            const dx = e.changedTouches[0].clientX - departX;
            if (Math.abs(dx) > 50) afficher(courant + (dx < 0 ? 1 : -1));
            departX = null;
        });

        // ----- Confirmation de suppression -----
        document.querySelectorAll('form.delete-photo-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (typeof Swal === 'undefined') {
                    if (confirm('Supprimer cette photo ?')) form.submit();
                    return;
                }

                // textContent : le titre n'est jamais interprété comme du HTML.
                const contenu = document.createElement('div');
                contenu.append('La photo ');
                const nom = document.createElement('strong');
                nom.textContent = '« ' + form.dataset.photoTitre + ' »';
                contenu.append(nom, ' sera définitivement supprimée.');

                Swal.fire({
                    title: 'Supprimer cette photo ?',
                    html: contenu,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler',
                    focusCancel: true,
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    });
    </script>

</x-admin>