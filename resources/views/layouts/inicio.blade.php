@extends('layouts.layout')

@section('content')

<ul class="nav nav-tabs" id="mainTabs">
    <li class="nav-item">
        <a class="nav-link active" data-tab="home">Inicio</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-tab="centros">Centros</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-tab="alumnos">Alumnos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-tab="calificaciones">Calificaciones</a>
    </li>
</ul>

<div id="tabContent" class="mt-3">
    <!-- Aquí se cargará contenido vía AJAX -->
    <h4>Bienvenido al Sistema Universidad</h4>
    <p>Selecciona una sección del menú.</p>
    
</div>
@endsection
@section('scripts')
<script>
document.querySelectorAll('[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        let section = tab.getAttribute('data-tab');
        fetch('/' + section)
            .then(r => r.text())
            .then(html => {
                document.getElementById('tabContent').innerHTML = html;
            });
    });
});
</script>

@endsection
