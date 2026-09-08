@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4 text-center">{{ $centro->telebachillerato }}</h2>

    <div class="card shadow mb-4">
        <div class="card-body">

            <p><strong>Clave:</strong> {{ $centro->clave }}</p>
            <p><strong>Clave CT:</strong> {{ $centro->clave_ct }}</p>
            <p><strong>Municipio:</strong> {{ $centro->municipio }}</p>
            <p><strong>Encargado:</strong> {{ $centro->encargado }}</p>
            <p><strong>Correo:</strong> {{ $centro->correo }}</p>

        </div>
    </div>

    <h4>Alumnos del Centro</h4>

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
@endsection
