<x-admin>
    <div class="container-fluid py-3">

        {{-- En-tête --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-envelope-open-text mr-2"></i>Messages de contact
                @if ($nonLus > 0)
                    <span class="badge badge-danger ml-2">{{ $nonLus }} non lu{{ $nonLus > 1 ? 's' : '' }}</span>
                @endif
            </h4>

            <div class="btn-group" role="group">
                <a href="{{ route('admin.contacts.index') }}"
                   class="btn btn-sm {{ request('filter') !== 'non_lus' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Tous
                </a>
                <a href="{{ route('admin.contacts.index', ['filter' => 'non_lus']) }}"
                   class="btn btn-sm {{ request('filter') === 'non_lus' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Non lus
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Tableau --}}
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Expéditeur</th>
                            <th>Objet</th>
                            <th>Reçu le</th>
                            <th>Lu le</th>
                            <th class="text-right" style="width: 130px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr class="{{ $contact->estLu() ? '' : 'font-weight-bold table-warning' }}">
                                <td class="text-center">
                                    <i class="fas {{ $contact->estLu() ? 'fa-envelope-open text-muted' : 'fa-envelope text-danger' }}"></i>
                                </td>
                                <td>
                                    {{ $contact->nom }}<br>
                                    <small class="text-muted font-weight-normal">{{ $contact->email }}</small>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($contact->objet, 50) }}</td>
                                <td>{{ $contact->envoye_le?->format('d/m/Y H:i') }}</td>
                                <td>{{ $contact->lu_le?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-right">
                                    {{-- Lecture : page dédiée --}}
                                    <a href="{{ route('admin.contacts.show', $contact) }}"
                                       class="btn btn-sm btn-outline-primary" title="Lire">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Suppression : modal de confirmation --}}
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-supprimer"
                                            data-toggle="modal"
                                            data-target="#modalSuppression"
                                            data-action="{{ route('admin.contacts.destroy', $contact) }}"
                                            data-nom="{{ $contact->nom }}"
                                            data-objet="{{ $contact->objet }}"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aucun message à afficher.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($contacts->hasPages())
                <div class="card-footer">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modal de confirmation de suppression --}}
    <div class="modal fade" id="modalSuppression" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form id="formSuppression" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Confirmer la suppression
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fermer">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-1">Voulez-vous vraiment supprimer le message de <strong id="sup-nom"></strong> ?</p>
                        <p class="text-muted mb-0">Objet : <span id="sup-objet"></span></p>
                        <p class="text-danger small mt-3 mb-0">Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.btn-supprimer').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('formSuppression').action = btn.dataset.action;
                    document.getElementById('sup-nom').textContent    = btn.dataset.nom;
                    document.getElementById('sup-objet').textContent  = btn.dataset.objet || '(Sans objet)';
                });
            });
        </script>
    @endpush
</x-admin>