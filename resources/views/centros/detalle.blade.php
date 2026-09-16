@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="mb-0">{{ $centro->telebachillerato ?? 'Centro' }}</h2>
    <a href="{{ url('/centros') }}" class="btn btn-outline-secondary btn-sm">← Regresar</a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Clave:</strong> {{ $centro->clave ?? 'No disponible' }}</p>
                    <p class="mb-2"><strong>Clave CT:</strong> {{ $centro->clave_ct ?? 'No disponible' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Municipio:</strong> {{ $centro->municipio ?? 'No disponible' }}</p>
                    <p class="mb-2"><strong>Encargado:</strong> {{ $centro->encargado ?? 'No disponible' }}</p>
                    <p class="mb-2"><strong>Correo:</strong> {{ $centro->correo ?? 'No disponible' }}</p>
                </div>
                </div>
        </div>
    </div>

    <h4 class="mb-3">Alumnos del Centro</h4>

@if(alumnos->isEmpty())
<div class="alert alert-info" role="alert">
    No hay alumnos registrados en este centro.
</div>
@else
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Matrícula</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alumnos as $a)
                <tr>
                    <td>{{ $a->matricula }}</td>
                    <td>{{ $a->nombre }} {{ $a->paterno }} {{ $a->materno }}</td>
                    <td>
                        <a href="/alumnos/{{ $a->id }}" class="btn btn-sm btn-primary">Ver</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endif
</div>
@endsection
