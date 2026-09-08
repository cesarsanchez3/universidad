@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h2 class="mb-4 text-center">Detalle del Alumno</h2>

    <div id="mensaje" class="alert d-none"></div>

    <div class="card shadow">
        <div class="card-body">

            <form id="formEditar">

                <input type="hidden" name="id" value="{{ $alumno->id }}">

                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="nombre" class="form-control"
                           value="{{ $alumno->nombre }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Apellido Paterno</label>
                    <input type="text" name="paterno" class="form-control"
                           value="{{ $alumno->paterno }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Apellido Materno</label>
                    <input type="text" name="materno" class="form-control"
                           value="{{ $alumno->materno }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Estatus</label>
                    <input type="text" name="estatus" class="form-control"
                           value="{{ $alumno->estatus }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Municipio de Residencia</label>
                    <input type="text" name="municipio_residencia" class="form-control"
                           value="{{ $alumno->municipio_residencia }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">País de Nacimiento</label>
                    <input type="text" name="pais_nacimiento" class="form-control"
                           value="{{ $alumno->pais_nacimiento }}">
                </div>

                <button class="btn btn-primary w-100">Guardar Cambios</button>

            </form>

        </div>
    </div>

</div>

<script>
document.getElementById('formEditar').addEventListener('submit', async function(e) {
    e.preventDefault();

    const mensaje = document.getElementById('mensaje');
    mensaje.classList.add('d-none');

    const formData = new FormData(this);

    const response = await fetch('/alumnos/update', {
        method: 'POST',
        body: formData
    });

    const data = await response.json();

    mensaje.classList.remove('d-none');
    mensaje.classList.add('alert-success');
    mensaje.innerHTML = "Datos actualizados correctamente";
});
</script>

@endsection
