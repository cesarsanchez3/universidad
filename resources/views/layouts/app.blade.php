<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Systema Escolar Universidad</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        nav img {
            height: 35px;
            margin-right: 8px;
        }
    </style>
    
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Sistema Escolar</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nmenuNav"
                aria-controls="menuNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuNav">
            <ul class=" navbar-nav me-auto">

                <li class="nav-item d-flex align-items-center">
                    <a class="nav-link d-flex align-items-center" href="/centros">
                        <img src="{{ asset('centros.png') }}" 
                             style="height:35px; margin-right:8px;">
                        Centros
                    </a>
                </li>

                <li class="nav-item d-flex align-items-center">
                    <a class="nav-link d-flex align-items-center" href="/alumnos">
                        <img src="{{ asset('alumnos.png') }}" 
                             style="height:35px; margin-right:8px;">
                        Alumnos
                    </a>
                </li>

                <li class="nav-item d-flex align-items-center">
                    <a class="nav-link d-flex align-items-center" href="/calificaciones">
                        <img src="{{ asset('calificaciones.png') }}"  
                             style="height:35px; margin-right:8px;">
                        Calificaciones
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <a class="nav-link d-flex align-items-center" href="/importar-todo">
                    Importar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
   @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')
</body>
</html>
