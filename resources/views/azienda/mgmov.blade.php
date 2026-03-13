@include('azienda.common.header')

<div class="container-fluid">
        <h2 class="mb-4">Movimenti di Magazzino</h2>

        <!-- Filtri -->
        <form method="GET" action="{{ route('magazzino.movimenti') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Causale</label>
                    <select name="causale" class="form-control">
                        <option value="">Tutte</option>
                        <option value="Carico" {{ request('causale') == 'Carico' ? 'selected' : '' }}>Carico</option>
                        <option value="Scarico" {{ request('causale') == 'Scarico' ? 'selected' : '' }}>Scarico</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Articolo</label>
                    <select name="articolo" class="form-control">
                        <option value="">Tutti</option>
                        @foreach ($articoli as $articolo)
                            <option value="{{ $articolo->id }}" {{ request('articolo') == $articolo->id ? 'selected' : '' }}>
                                {{ $articolo->titolo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Data Inizio</label>
                    <input type="date" name="data_inizio" class="form-control" value="{{ request('data_inizio') }}">
                </div>

                <div class="col-md-3">
                    <label>Data Fine</label>
                    <input type="date" name="data_fine" class="form-control" value="{{ request('data_fine') }}">
                </div>

                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-primary">Filtra</button>
                    <a href="{{ route('magazzino.movimenti') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <!-- Tabella Movimenti -->
        <table class="table table-bordered">
            <thead class="table-dark">
            <tr>
                <th>Data</th>
                <th>Utente</th>
                <th>Articolo</th>
                <th>Causale</th>
                <th>Quantità</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($movimenti as $movimento)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movimento->datamov)->format('d/m/Y H:i') }}</td>
                    <td>{{ $movimento->utente_nome }} {{ $movimento->utente_cognome }}</td>
                    <td>{{ $movimento->articolo_nome }}</td>
                    <td>
                        <span class="badge {{ $movimento->causale == 'Carico' ? 'bg-success' : 'bg-danger' }}">
                            {{ $movimento->causale }}
                        </span>
                    </td>
                    <td>{{ $movimento->qta }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nessun movimento registrato</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

@include('azienda.common.footer')
