<x-admin>
    @section('title', ($slide->exists ? 'Modifier' : 'Nouveau') . ' slide — Page d\'accueil')

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-0">
                {{ $slide->exists ? 'Modifier le slide' : 'Nouveau slide' }}
            </h1>
            <small class="text-muted">
                {{ $slide->exists ? 'Mettez à jour les informations du slide' : 'Créez un nouveau slide pour le hero slider' }}
            </small>
        </div>
        <a href="{{ route('admin.slides.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    <form action="{{ $slide->exists ? route('admin.slides.update', $slide) : route('admin.slides.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="card">
        @csrf
        @if ($slide->exists)
            @method('PUT')
        @endif

        <div class="card-body">
            <div class="row g-3">
                {{-- Titre --}}
                <div class="col-md-8">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text"
                           id="titre"
                           name="titre"
                           class="form-control @error('titre') is-invalid @enderror"
                           value="{{ old('titre', $slide->titre) }}"
                           maxlength="255">
                    @error('titre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Ordre --}}
                <div class="col-md-4">
                    <label for="ordre" class="form-label">Ordre d'affichage</label>
                    <input type="number"
                           id="ordre"
                           name="ordre"
                           class="form-control @error('ordre') is-invalid @enderror"
                           value="{{ old('ordre', $slide->ordre ?? 0) }}"
                           min="0">
                    @error('ordre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Image --}}
                <div class="col-12">
                    <label for="image" class="form-label">
                        Image {{ $slide->exists ? '(laisser vide pour conserver)' : '' }}
                        @if (!$slide->exists)
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    <input type="file"
                           id="image"
                           name="image"
                           accept="image/jpeg,image/png,image/webp"
                           class="form-control @error('image') is-invalid @enderror"
                           {{ $slide->exists ? '' : 'required' }}>
                    <div class="form-text">
                        Formats acceptés : JPG, PNG, WEBP — taille maximale : 3 Mo.
                    </div>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if ($slide->exists && $slide->image)
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">Image actuelle :</small>
                            <img src="{{ asset($slide->image) }}"
                                 alt="{{ $slide->titre }}"
                                 style="max-width:280px;height:auto;border-radius:8px;border:1px solid #dee2e6;">
                        </div>
                    @endif
                </div>

                {{-- Actif --}}
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                               id="actif"
                               name="actif"
                               value="1"
                               class="form-check-input"
                               {{ old('actif', $slide->actif) ? 'checked' : '' }}>
                        <label for="actif" class="form-check-label">
                            Slide actif (visible sur la page d'accueil)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('admin.slides.index') }}" class="btn btn-outline-secondary">
                Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                {{ $slide->exists ? 'Enregistrer les modifications' : 'Créer le slide' }}
            </button>
        </div>
    </form>
</x-admin>