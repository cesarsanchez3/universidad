@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4 text-center">Alumnos TBC</h2>

    <div class="card shadow">
        <div class="card-body">

            <input type="text" id="buscar" class="form-control mb-3"
                   placeholder="Buscar alumno por nombre o matricula">
                   <button id="btnBuscar" class="btn btn-primary mb-3">Buscar</button>


            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Matricula</th>
                        <th>Telebachillerato</th>
                        <th>Centro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaAlumnos"></tbody>
            </table>

            <div class="d-flex justify-content-between mt-3">
                <button id="prev" class="btn btn-secondary">Anterior</button>
                <button id="next" class="btn btn-secondary">Siguiente</button>
            </div>

        </div>
    </div>

</div>

<script>
let page = 1;

async function cargarAlumnos() {
    const search = document.getElementById('buscar').value;

    const response = await fetch(`/alumnos/list?search=${search}&page=${page}`);
    const data = await response.json();

    const tabla = document.getElementById('tablaAlumnos');
    tabla.innerHTML = '';

    data.data.forEach(a => {
        tabla.innerHTML += `
            <tr>
                <td>${a.matricula}</td>
                <td>${a.nombre} ${a.paterno} ${a.materno}</td>
                <td>${a.centro}</td>
                <td>
                    <a href="/alumnos/${a.id}" class="btn btn-sm btn-primary">Ver</a>
                </td>
            </tr>
        `;
    });
}

document.getElementById('buscar').addEventListener('keyup', () => {
    page = 1;
    cargarAlumnos();
});

document.getElementById('prev').addEventListener('click', () => {
    if (page > 1) {
        page--;
        cargarAlumnos();
    }
});

document.getElementById('next').addEventListener('click', () => {
    page++;
    cargarAlumnos();
});

cargarAlumnos();
</script>

@endsection
