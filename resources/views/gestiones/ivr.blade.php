@extends('layouts.app')

@section('title', 'Gestiones IVR')

@section('content')
<h3>Llamadas IVR</h3>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header">
        <strong>Consulta IVR (spLlamadasIVR)</strong>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('gestiones.ivr') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="datetime-local" name="desde" value="{{ $desde }}" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="datetime-local" name="hasta" value="{{ $hasta }}" class="form-control" required>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    Consultar IVR
                </button>
            </div>
        </form>
    </div>
</div>

@if(isset($rows) && $rows->count())
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Campaña</th>
                        <th>Dialbase</th>
                        <th>Doc</th>
                        <th>Destino</th>
                        <th>Disposition</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td>{{ $r->calldate ?? '' }}</td>
                            <td>{{ $r->campaign ?? '' }}</td>
                            <td>{{ $r->dialbase ?? '' }}</td>
                            <td>{{ $r->doc ?? '' }}</td>
                            <td>{{ $r->dst ?? '' }}</td>
                            <td>{{ $r->disposition ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection