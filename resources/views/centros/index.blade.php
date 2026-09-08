@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4 text-center">Centros TBC</h2>

    <div class="card shadow">
        <div class="card-body">

            <input type="text" id="buscar" class="form-control mb-3"
                   placeholder="Buscar centro por clave, nombre o municipio">
                   <button id="btnBuscar" class="btn btn-primary mb-3">Buscar</button>


            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Telebachillerato</th>
                        <th>Municipio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCentros"></tbody>
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

async function cargarCentros() {
    const search = document.getElementById('buscar').value;

    const response = await fetch(`/centros/list?search=${search}&page=${page}`);
    const data = await response.json();

    const tabla = document.getElementById('tablaCentros');
    tabla.innerHTML = '';

    data.data.forEach(c => {
        tabla.innerHTML += `
            <tr>
                <td>${c.clave}</td>
                <td>${c.telebachillerato}</td>
                <td>${c.municipio}</td>
                <td>
                    <a href="/centros/${c.id}" class="btn btn-sm btn-primary">Ver</a>
                </td>
            </tr>
        `;
    });
}

document.getElementById('buscar').addEventListener('keyup', () => {
    page = 1;
    cargarCentros();
});

document.getElementById('prev').addEventListener('click', () => {
    if (page > 1) {
        page--;
        cargarCentros();
    }
});

document.getElementById('next').addEventListener('click', () => {
    page++;
    cargarCentros();
});

cargarCentros();
</script>

@endsection
