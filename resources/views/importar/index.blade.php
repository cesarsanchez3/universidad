@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4 text-center">Importación de Centros y Alumnos</h2>

    {{-- Mensajes --}}
    <div id="mensaje" class="alert d-none"></div>

    <div class="card shadow">
        <div class="card-body">

            <form id="formImportar" action="/importar-todo" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Archivo de Centros (CentrosTBC.csv)</label>
                    <input type="file" name="CentrosTBC" class="form-control" accept=".csv" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Archivo de Alumnos (AlumnosTBC.csv)</label>
                    <input type="file" name="AlumnosTBC" class="form-control" accept=".csv" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Importar Datos
                </button>

            </form>

        </div>
    </div>

</div>

<script>
document.getElementById('formImportar').addEventListener('submit', async function(e) {
    e.preventDefault();

    const mensaje = document.getElementById('mensaje');
    // Limpiar mensajes previos
    mensaje.classList.add('d-none');
    mensaje.classList.remove('alert-danger', 'alert-success');

    const formData = new FormData(this);

    try {
        const response = await fetch('/importar-todo', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        mensaje.classList.remove('d-none');

        // Mostrar errores si los hay
        if (data.error) {
            mensaje.classList.add('alert-danger');
            mensaje.innerHTML = data.error;
            return;
        }

        // Mostrar resultados de la importación
        mensaje.classList.add('alert-success');
        mensaje.innerHTML = `
            <strong>Importación completada:</strong><br>
            Centros insertados: ${data.centros.insertados}<br>
            Alumnos insertados: ${data.alumnos.insertados}<br>
            <hr>
            <strong>Errores Centros:</strong><br>
            ${data.centros.errores.length ? data.centros.errores.join('<br>') : 'Sin errores'}<br><br>
            <strong>Errores Alumnos:</strong><br>
            ${data.alumnos.errores.length ? data.alumnos.errores.join('<br>') : 'Sin errores'}
        `;

    } catch (error) {
        mensaje.classList.remove('d-none');
        mensaje.classList.add('alert-danger');
        mensaje.innerHTML = "Error al enviar la solicitud.";
    }
});
</script>

@endsection
