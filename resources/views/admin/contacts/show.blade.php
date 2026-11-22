<x-admin>
    <div class="container-fluid py-3">

        {{-- En-tête --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-envelope-open-text mr-2"></i>Message de contact
            </h4>
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Retour à la liste
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">{{ $contact->objet ?: '(Sans objet)' }}</h5>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <strong>Expéditeur :</strong> {{ $contact->nom }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Email :</strong>
                        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Téléphone :</strong> {{ $contact->telephone ?: '—' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Reçu le :</strong> {{ $contact->envoye_le?->format('d/m/Y à H:i') }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Adresse IP :</strong> {{ $contact->ip ?: '—' }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Lu le :</strong> {{ $contact->lu_le?->format('d/m/Y à H:i') ?? '—' }}
                    </div>
                </div>

                <hr>

                <h6 class="text-muted text-uppercase">Message</h6>
                <div class="p-3 bg-light rounded">
                    {!! nl2br(e($contact->message)) !!}
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                {{-- <a href="mailto:{{ $contact->email }}?subject={{ rawurlencode('Re: ' . $contact->objet) }}"
                   class="btn btn-primary">
                    <i class="fas fa-reply mr-1"></i>Répondre
                </a> --}}

                <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#modalSuppression">
                    <i class="fas fa-trash mr-1"></i>Supprimer
                </button>
            </div>
        </div>
    </div>

    {{-- Modal de confirmation de suppression --}}
    <div class="modal fade" id="modalSuppression" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}">
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
                        <p class="mb-1">Voulez-vous vraiment supprimer le message de <strong>{{ $contact->nom }}</strong> ?</p>
                        <p class="text-muted mb-0">Objet : {{ $contact->objet ?: '(Sans objet)' }}</p>
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
</x-admin>