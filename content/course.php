<?php
// Estructura del curso. Cada lección vive en content/lessons/<slug>.php
return [
    [
        'slug' => 'fundamentos', 'tier' => 'facil', 'icon' => '🌱', 'color' => '#3776ab', 'achievement' => 'mod_fundamentos',
        'title' => 'Primeros pasos',
        'description' => 'Tu primer programa, variables, números y textos.',
        'lessons' => ['hola-python', 'variables', 'numeros', 'textos'],
    ],
    [
        'slug' => 'decisiones', 'tier' => 'facil', 'icon' => '🔀', 'color' => '#8b5cf6',
        'title' => 'Tomando decisiones',
        'description' => 'Verdadero o falso, comparaciones y el poderoso if.',
        'lessons' => ['booleanos', 'condicionales'],
    ],
    [
        'slug' => 'colecciones', 'tier' => 'normal', 'icon' => '📦', 'color' => '#0ea5e9',
        'title' => 'Listas, bucles y diccionarios',
        'description' => 'Guarda muchos datos y recórrelos automáticamente.',
        'lessons' => ['listas', 'bucles', 'diccionarios'],
    ],
    [
        'slug' => 'funciones', 'tier' => 'normal', 'icon' => '🧰', 'color' => '#f97316',
        'title' => 'Funciones',
        'description' => 'Crea tus propias herramientas y calcula estadísticas a mano.',
        'lessons' => ['funciones', 'estadistica-a-mano'],
    ],
    [
        'slug' => 'numpy', 'tier' => 'dificil', 'icon' => '🔢', 'color' => '#14b8a6', 'achievement' => 'mod_numpy',
        'title' => 'NumPy: números a toda velocidad',
        'description' => 'Arreglos, operaciones en masa y estadística rápida.',
        'lessons' => ['numpy-arrays', 'numpy-estadistica'],
    ],
    [
        'slug' => 'pandas', 'tier' => 'dificil', 'icon' => '🐼', 'color' => '#ec4899', 'achievement' => 'mod_pandas',
        'title' => 'Análisis de datos con pandas',
        'description' => 'Tablas, archivos CSV, filtros, agrupaciones y limpieza.',
        'lessons' => ['pandas-intro', 'pandas-csv', 'pandas-filtrar', 'pandas-agrupar', 'pandas-limpieza'],
    ],
    [
        'slug' => 'graficos', 'tier' => 'experto', 'icon' => '📈', 'color' => '#ca8a04', 'achievement' => 'mod_graficos',
        'title' => 'Visualización de datos',
        'description' => 'Convierte números en gráficos que cuentan historias.',
        'lessons' => ['graficos-matplotlib', 'graficos-pandas'],
    ],
    [
        'slug' => 'proyecto', 'tier' => 'experto', 'icon' => '🏆', 'color' => '#16a34a', 'achievement' => 'mod_proyecto',
        'title' => 'Proyecto final',
        'description' => 'Analiza las ventas de una tienda de principio a fin.',
        'lessons' => ['proyecto-ventas'],
    ],
    // ===================== NEW GAME PLUS =====================
    [
        'slug' => 'ngp-python', 'tier' => 'ngplus', 'ngplus' => true, 'icon' => '🌀', 'color' => '#a34dff',
        'title' => 'Python pro',
        'description' => 'Comprensiones, manejo de errores, lambda y apply.',
        'lessons' => ['ngp-comprensiones', 'ngp-errores', 'ngp-lambda'],
    ],
    [
        'slug' => 'ngp-pandas', 'tier' => 'ngplus', 'ngplus' => true, 'icon' => '⚡', 'color' => '#d946ef',
        'title' => 'pandas avanzado',
        'description' => 'Unir tablas, series de tiempo, regex y melt/pivot.',
        'lessons' => ['ngp-merge', 'ngp-tiempo', 'ngp-texto', 'ngp-reshape'],
    ],
    [
        'slug' => 'ngp-excel', 'tier' => 'ngplus', 'ngplus' => true, 'icon' => '📗', 'color' => '#10b981',
        'title' => 'Excel pro',
        'description' => 'Libros de varias hojas, formato, colores y fórmulas con openpyxl.',
        'lessons' => ['ngp-excel', 'ngp-openpyxl'],
    ],
    [
        'slug' => 'ngp-estadistica', 'tier' => 'ngplus', 'ngplus' => true, 'icon' => '🔮', 'color' => '#f59e0b', 'achievement' => 'mod_ngplus',
        'title' => 'Estadística y predicción',
        'description' => 'Atípicos, correlación, regresión y el Jefe Final NG+.',
        'lessons' => ['ngp-atipicos', 'ngp-regresion', 'ngp-jefe-final'],
    ],
    // ===================== INFIERNO =====================
    [
        'slug' => 'inf-algoritmos', 'tier' => 'infierno', 'modo' => 'infierno', 'icon' => '🔥', 'color' => '#ff3d00',
        'title' => 'Círculo I · Algoritmos malditos',
        'description' => 'Generadores, clases y eficiencia: código que no se rompe ni se arrastra.',
        'lessons' => ['inf-generadores', 'inf-clases', 'inf-algoritmos'],
    ],
    [
        'slug' => 'inf-pandas', 'tier' => 'infierno', 'modo' => 'infierno', 'icon' => '😈', 'color' => '#ff6a00',
        'title' => 'Círculo II · pandas demoníaco',
        'description' => 'transform, rankings, ventanas por grupo y vectorización extrema.',
        'lessons' => ['inf-transform', 'inf-ventanas', 'inf-vectorizar'],
    ],
    [
        'slug' => 'inf-abismo', 'tier' => 'infierno', 'modo' => 'infierno', 'icon' => '💀', 'color' => '#b91c1c', 'achievement' => 'mod_infierno',
        'title' => 'Círculo III · El abismo',
        'description' => 'Pruebas de hipótesis, clasificación y el Jefe Final: Lucifer.',
        'lessons' => ['inf-hipotesis', 'inf-clasificacion', 'inf-lucifer'],
    ],
];
