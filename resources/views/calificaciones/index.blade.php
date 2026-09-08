@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4 text-center">Captura de Calificaciones</h2>

    <div id="mensaje" class="alert d-none"></div>

    <div class="card shadow">
        <div class="card-body">

            {{-- Selección de alumno --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Alumno</label>
                <select id="alumno" class="form-select">
                    <option value="">Seleccione un alumno...</option>
                </select>
            </div>

            {{-- Calificaciones --}}
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Parcial 1</label>
                    <input type="number" id="p1" class="form-control" min="0" max="10">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Parcial 2</label>
                    <input type="number" id="p2" class="form-control" min="0" max="10">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Parcial 3</label>
                    <input type="number" id="p3" class="form-control" min="0" max="10">
                </div>
            </div>

            {{-- Promedio automático --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Promedio</label>
                <input type="text" id="promedio" class="form-control" readonly>
            </div>

            <button id="guardar" class="btn btn-primary w-100">Guardar Calificaciones</button>

        </div>
    </div>

</div>

<script>
// ---------------------------------------------------------
// Cargar alumnos en el select
// ---------------------------------------------------------
async function cargarAlumnos() {
    const response = await fetch('/alumnos/list?page=1');
    const data = await response.json();

    const select = document.getElementById('alumno');
    data.data.forEach(a => {
        select.innerHTML += `<option value="${a.id}">${a.nombre} ${a.paterno} ${a.materno}</option>`;
    });
}

cargarAlumnos();

// ---------------------------------------------------------
// Cálculo automático del promedio
// ---------------------------------------------------------
function calcularPromedio() {
    const p1 = parseFloat(document.getElementById('p1').value) || 0;
    const p2 = parseFloat(document.getElementById('p2').value) || 0;
    const p3 = parseFloat(document.getElementById('p3').value) || 0;

    const promedio = ((p1 + p2 + p3) / 3).toFixed(2);
    document.getElementById('promedio').value = promedio;
}

document.getElementById('p1').addEventListener('input', calcularPromedio);
document.getElementById('p2').addEventListener('input', calcularPromedio);
document.getElementById('p3').addEventListener('input', calcularPromedio);

// ---------------------------------------------------------
// Guardar calificaciones vía AJAX
// ---------------------------------------------------------
document.getElementById('guardar').addEventListener('click', async () => {

    const mensaje = document.getElementById('mensaje');
    mensaje.classList.add('d-none');

    const alumno = document.getElementById('alumno').value;
    const p1 = document.getElementById('p1').value;
    const p2 = document.getElementById('p2').value;
    const p3 = document.getElementById('p3').value;
    const promedio = document.getElementById('promedio').value;

    // Validaciones
    if (!alumno) {
        mensaje.classList.remove('d-none', 'alert-success');
        mensaje.classList.add('alert-danger');
        mensaje.innerHTML = "Debe seleccionar un alumno";
        return;
    }

    if (p1 < 0 || p1 > 10 || p2 < 0 || p2 > 10 || p3 < 0 || p3 > 10) {
        mensaje.classList.remove('d-none', 'alert-success');
        mensaje.classList.add('alert-danger');
        mensaje.innerHTML = "Las calificaciones deben estar entre 0 y 10";
        return;
    }

    const response = await fetch('/calificaciones/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            alumno_id: alumno,
            p1: p1,
            p2: p2,
            p3: p3,
            promedio: promedio
        })
    });

    const data = await response.json();

    mensaje.classList.remove('d-none');
    mensaje.classList.add('alert-success');
    mensaje.innerHTML = "Calificaciones guardadas correctamente";
});
</script>

@endsection
