# 🐍 PyAprende

Plataforma web para **aprender Python desde cero** de forma divertida, con enfoque en **análisis de datos**.
Pensada para personas sin ningún conocimiento previo de programación.

- 🧩 **21 micro-lecciones en 8 módulos**: de `print()` a pandas, gráficos y un proyecto final.
- 💻 **Python corre en el navegador** gracias a [Pyodide](https://pyodide.org) (WebAssembly). No hay que instalar Python.
- ✅ **Ejercicios que se corrigen solos**, con pistas, solución y errores explicados en español.
- 🎮 **Gamificación**: XP, 8 niveles, rachas diarias, 14 logros, ranking, confeti y sonidos.
- 📊 **Datos reales de práctica**: `ventas.csv` (300 ventas), `ventas_sucias.csv` (para limpieza) y `clima.csv`.
- 🧪 **Laboratorio** libre con numpy, pandas y matplotlib.

## Ruta de aprendizaje

| # | Módulo | Lecciones |
|---|--------|-----------|
| 1 | 🌱 Primeros pasos | ¡Hola, Python!, Variables, Números, Textos |
| 2 | 🔀 Tomando decisiones | Booleanos, if / elif / else |
| 3 | 📦 Listas, bucles y diccionarios | Listas, for / while, Diccionarios |
| 4 | 🧰 Funciones | Funciones, Estadística a mano (media, mediana…) |
| 5 | 🔢 NumPy | Arrays y vectorización, Estadística y máscaras |
| 6 | 🐼 pandas | DataFrames, Leer CSV, Filtrar, groupby, Limpieza de datos |
| 7 | 📈 Visualización | matplotlib, Gráficos desde pandas |
| 8 | 🏆 Proyecto final | Reporte anual de ventas de "TiendaPy" |

## Instalación con XAMPP

**No necesitas nada aparte de XAMPP** (Apache + PHP 8.1 o superior + MySQL/MariaDB) y un navegador moderno con internet.

1. **Copia el proyecto** a la carpeta `htdocs` de XAMPP, por ejemplo:
   `C:\xampp\htdocs\learnpython`
2. **Inicia Apache y MySQL** desde el panel de control de XAMPP.
3. **Crea la base de datos**: abre <http://localhost/phpmyadmin>, ve a la pestaña **Importar** y
   selecciona el archivo `database/schema.sql`. Eso crea la base `pyaprende` con sus tablas.
4. **Configuración** (opcional): copia `config/config.example.php` como `config/config.php`.
   Si tu MySQL tiene contraseña para `root`, escríbela ahí. Si no existe `config.php`, se usan
   los valores del ejemplo (usuario `root` sin contraseña, lo normal en XAMPP).
5. Abre <http://localhost/learnpython> y ¡a aprender! 🎉

### ¿Y sin internet?

Python (Pyodide) y el editor de código se descargan de un CDN la primera vez. Si quieres usar el sitio
totalmente offline (por ejemplo, en un aula sin internet):

1. Descarga el paquete completo `pyodide-0.27.2.tar.bz2` de
   <https://github.com/pyodide/pyodide/releases/tag/0.27.2> y descomprímelo en `assets/pyodide/`
   (deben quedar archivos como `assets/pyodide/pyodide.js` y `assets/pyodide/pandas-*.whl`).
2. En `config/config.php` cambia `pyodide_url` a `'assets/pyodide/'`.

Si el editor con colores no puede cargar, el sitio usa automáticamente un editor sencillo.

## Estructura del proyecto

```
index.php, learn.php, lesson.php,    Páginas del sitio (PHP)
playground.php, ranking.php,
profile.php, login.php, register.php
api/progress.php                     API JSON: XP, pasos, lecciones, logros
includes/                            Arranque, sesión, BD, niveles y logros
content/course.php                   Módulos y orden de las lecciones
content/lessons/*.php                Contenido de cada lección
assets/js/py-worker.js               Web Worker que ejecuta Python con Pyodide
assets/py/harness.py                 Ejecuta el código, captura salida/gráficos y corrige
assets/js/lesson.js                  Motor de lecciones (pasos, quiz, retos)
data/*.csv                           Datasets de práctica
database/schema.sql                  Esquema MySQL
tools/test_lessons.py                Prueba automática de todo el contenido
```

## Cómo se corrige un ejercicio

Cada ejercicio tiene un `check`: código Python que se ejecuta después del código del estudiante,
en el mismo espacio de variables. Usa `assert` con un mensaje amable en español. Además tiene
acceso a `_salida` (lo que imprimió), `_codigo` (el código escrito) e `_imagenes` (cuántos gráficos generó).

```php
'check' => <<<'PY'
assert total == 135, "total debe ser precio * cantidad."
assert "135" in _salida, "No olvides mostrar el total con print()."
PY,
```

El código corre dentro de un Web Worker: si alguien escribe un bucle infinito, se detiene a los
12 segundos y Python se reinicia sin congelar la página.

## Agregar o editar lecciones

1. Crea `content/lessons/mi-leccion.php` (copia una existente como plantilla). Tipos de paso:
   `text` (teoría), `example` (código ejecutable), `quiz` (opción múltiple) y `exercise` (reto con corrección).
2. Agrega el slug en `content/course.php`.
3. Verifica que todo funcione:

```bash
pip install numpy pandas matplotlib
python tools/test_lessons.py
```

El script comprueba que cada solución pase su revisión, que el código inicial **no** la pase y que los ejemplos corran sin error.

> ⚠️ En strings PHP con comillas dobles, escapa el `$` de las f-strings de Python (`\$`), o usa nowdoc (`<<<'PY'`).
