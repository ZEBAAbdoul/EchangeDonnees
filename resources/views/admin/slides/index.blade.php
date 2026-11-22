<x-admin>
    <x-slot:title>{{ 'Slides — Page d\'accueil' }}</x-slot:title>

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-0">Slides de la page d'accueil</h1>
            <small class="text-muted">Gérez les images du hero slider</small>
        </div>
        <a href="{{ route('admin.slides.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau slide
        </a>
    </div>

    {{-- Message de succès --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    {{-- Cartes statistiques --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;">
                        <i class="fas fa-images"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total</div>
                        <div class="h5 mb-0">{{ $slides->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Actifs</div>
                        <div class="h5 mb-0">{{ $slides->where('actif', true)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center"
                        style="width:48px;height:48px;">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Masqués</div>
                        <div class="h5 mb-0">{{ $slides->where('actif', false)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Carte principale --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-semibold">Liste des slides</span>
            <div class="input-group input-group-sm" style="max-width:280px;">
                <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                <input type="search" id="slideSearch" class="form-control" placeholder="Rechercher un titre…"
                    aria-label="Rechercher un slide">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px">Ordre</th>
                            <th style="width:150px">Image</th>
                            <th>Titre</th>
                            <th style="width:110px">Statut</th>
                            <th class="text-end" style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="slidesTable">
                        @forelse ($slides as $slide)
                            <tr data-title="{{ Str::lower($slide->titre ?? '') }}">
                                {{-- Ordre --}}
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        #{{ $slide->ordre }}
                                    </span>
                                </td>

                                {{-- Image --}}
                                <td>
                                    @if ($slide->image)
                                        <img src="{{ asset($slide->image) }}" alt="{{ $slide->titre }}" loading="lazy"
                                            class="rounded shadow-sm" style="width:130px;height:72px;object-fit:cover;">
                                    @else
                                        <span class="text-muted small fst-italic">Aucune</span>
                                    @endif
                                </td>

                                {{-- Titre --}}
                                <td>
                                    @if ($slide->titre)
                                        <div class="fw-semibold">{{ $slide->titre }}</div>
                                    @else
                                        <span class="text-muted fst-italic">Sans titre</span>
                                    @endif
                                </td>

                                {{-- Statut --}}
                                <td>
                                    @if ($slide->actif)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-circle-check me-1"></i>Actif
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="fas fa-circle-xmark me-1"></i>Masqué
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-end">
                                    <a href="{{ route('admin.slides.edit', $slide) }}"
                                        class="btn btn-sm btn-outline-primary" title="Modifier"
                                        aria-label="Modifier le slide {{ $slide->titre }}">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    {{-- <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                        aria-label="Supprimer le slide {{ $slide->titre }}" data-bs-toggle="modal"
                                        data-bs-target="#deleteSlideModal"
                                        data-delete-url="{{ route('admin.slides.destroy', $slide) }}"
                                        data-slide-title="{{ $slide->titre ?: 'Sans titre' }}"
                                        data-slide-image="{{ $slide->image ? asset($slide->image) : '' }}">
                                        <i class="fas fa-trash"></i>
                                    </button> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-images fa-2x mb-3 d-block opacity-50"></i>
                                        <p class="mb-2">Aucun slide pour le moment.</p>
                                        <a href="{{ route('admin.slides.create') }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus me-1"></i> Créer le premier slide
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- MODAL DE CONFIRMATION SUPPR. --}}
    {{-- ============================= --}}
    <div class="modal fade" id="deleteSlideModal" tabindex="-1" aria-labelledby="deleteSlideModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteSlideModalLabel">
                        <i class="fas fa-triangle-exclamation me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="d-flex align-items-start gap-3">
                        <img id="deleteSlidePreview" src="" alt="" class="rounded shadow-sm d-none"
                            style="width:120px;height:68px;object-fit:cover;">
                        <div>
                            <p class="mb-2">Voulez-vous vraiment supprimer ce slide ?</p>
                            <p class="mb-0 fw-semibold" id="deleteSlideTitle"></p>
                            <small class="text-muted">Cette action est irréversible.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <form id="deleteSlideForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = document.getElementById('deleteSlideModal');
            const form = document.getElementById('deleteSlideForm');
            if (!deleteModal || !form) return;

            deleteModal.addEventListener('show.bs.modal', (event) => {
                const button = event.relatedTarget;
                if (!button) return;

                const title = button.dataset.slideTitle || '';
                const image = button.dataset.slideImage || '';

                form.action = button.dataset.deleteUrl;
                document.getElementById('deleteSlideTitle').textContent = title;

                const preview = document.getElementById('deleteSlidePreview');
                if (image) {
                    preview.src = image;
                    preview.alt = title;
                    preview.classList.remove('d-none');
                } else {
                    preview.removeAttribute('src');
                    preview.classList.add('d-none');
                }
            });

            // Sécurité : ne jamais soumettre avec une action vide
            form.addEventListener('submit', (e) => {
                if (!form.getAttribute('action')) {
                    e.preventDefault();
                    console.error('Action de suppression vide.');
                }
            });
        });
    </script>
</x-admin>
