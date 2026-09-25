<?php
return [
    'title' => 'Hola, pandas 🐼',
    'icon' => '🐼',
    'minutes' => 10,
    'summary' => 'Series y DataFrames: tablas de datos dentro de Python.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Excel con superpoderes',
            'html' => <<<'HTML'
<p><b>pandas</b> es LA librería de análisis de datos en Python. Si alguna vez usaste Excel, te va a resultar familiar: trabaja con <b>tablas</b>.</p>
<ul>
  <li>Una <b>Series</b> es una sola columna de datos (con etiquetas).</li>
  <li>Un <b>DataFrame</b> es una tabla completa: varias columnas, cada una con nombre.</li>
</ul>
<pre>import pandas as pd   # "pd" es el apodo universal</pre>
<div class="analogy">📒 Un DataFrame es una hoja de Excel: filas, columnas con encabezado, y un número de fila a la izquierda (el <b>índice</b>). La diferencia es que puedes manipular millones de filas con una línea de código.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Tu primer DataFrame',
            'html' => '<p>Se puede crear a partir de un diccionario: cada clave es una columna. Fíjate que si la última línea es una tabla, se muestra bonita automáticamente (como en Jupyter). <i>La primera vez, pandas tarda unos segundos en cargar.</i></p>',
            'code' => <<<'PY'
import pandas as pd

empleados = pd.DataFrame({
    "nombre": ["Ana", "Luis", "Carla", "Pedro", "Sofía"],
    "depto": ["Ventas", "TI", "Ventas", "RH", "TI"],
    "salario": [18000, 25000, 21000, 17000, 27000],
    "antiguedad": [3, 5, 2, 7, 1],
})

empleados
PY,
        ],
        [
            'type' => 'example',
            'title' => 'Columnas = Series',
            'html' => '<p>Con <code>df["columna"]</code> tomas una columna (es una Series) y puedes calcular sobre ella, igual que con NumPy:</p>',
            'code' => <<<'PY'
import pandas as pd

empleados = pd.DataFrame({
    "nombre": ["Ana", "Luis", "Carla", "Pedro", "Sofía"],
    "depto": ["Ventas", "TI", "Ventas", "RH", "TI"],
    "salario": [18000, 25000, 21000, 17000, 27000],
})

print(empleados["salario"])
print("Promedio:", empleados["salario"].mean())
print("Máximo:", empleados["salario"].max())

# Crear una columna nueva a partir de otra
empleados["salario_con_bono"] = empleados["salario"] * 1.10
empleados
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'En un DataFrame de ventas, ¿qué hace <code>df["precio"] * 2</code>?',
            'options' => ['Duplica las filas de la tabla', 'Multiplica por 2 cada valor de la columna precio', 'Da error porque es una tabla', 'Crea 2 columnas precio'],
            'answer' => 1,
            'explain' => 'Las columnas de pandas funcionan como arrays de NumPy: las operaciones se aplican a <b>cada valor</b>.',
        ],
        [
            'type' => 'text',
            'title' => 'Datos útiles de un DataFrame',
            'html' => <<<'HTML'
<pre>df.shape        # (filas, columnas)
df.columns      # nombres de las columnas
df.head(3)      # primeras 3 filas
df.tail(3)      # últimas 3 filas
df["depto"].value_counts()   # cuántas veces aparece cada valor
df["depto"].unique()         # valores distintos</pre>
<div class="tip">💡 <code>value_counts()</code> hace en una línea el "truco del conteo" que programaste con diccionarios.</div>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🏢 Nómina anual',
            'html' => <<<'HTML'
<p>Usando el DataFrame <code>empleados</code>:</p>
<ol>
  <li>Crea la columna <code>salario_anual</code> = salario × 12</li>
  <li>Guarda en <code>nomina_total</code> la suma de la columna <code>salario_anual</code></li>
  <li>Guarda en <code>por_depto</code> cuántos empleados hay en cada departamento (usa <code>value_counts()</code>)</li>
</ol>
HTML,
            'starter' => "import pandas as pd\n\nempleados = pd.DataFrame({\n    \"nombre\": [\"Ana\", \"Luis\", \"Carla\", \"Pedro\", \"Sofía\", \"Diego\"],\n    \"depto\": [\"Ventas\", \"TI\", \"Ventas\", \"RH\", \"TI\", \"Ventas\"],\n    \"salario\": [18000, 25000, 21000, 17000, 27000, 19500],\n})\n\n\nempleados\n",
            'hint' => ['<code>empleados["salario_anual"] = empleados["salario"] * 12</code>', '<code>nomina_total = empleados["salario_anual"].sum()</code>', '<code>por_depto = empleados["depto"].value_counts()</code>'],
            'solution' => "import pandas as pd\n\nempleados = pd.DataFrame({\n    \"nombre\": [\"Ana\", \"Luis\", \"Carla\", \"Pedro\", \"Sofía\", \"Diego\"],\n    \"depto\": [\"Ventas\", \"TI\", \"Ventas\", \"RH\", \"TI\", \"Ventas\"],\n    \"salario\": [18000, 25000, 21000, 17000, 27000, 19500],\n})\n\nempleados[\"salario_anual\"] = empleados[\"salario\"] * 12\nnomina_total = empleados[\"salario_anual\"].sum()\npor_depto = empleados[\"depto\"].value_counts()\nprint(nomina_total)\nprint(por_depto)\nempleados\n",
            'check' => <<<'PY'
assert "salario_anual" in empleados.columns, "Falta la columna salario_anual."
assert (empleados["salario_anual"] == empleados["salario"] * 12).all(), "salario_anual debe ser salario × 12."
assert nomina_total == 127500 * 12, f"nomina_total debería ser {127500 * 12}."
assert dict(por_depto) == {"Ventas": 3, "TI": 2, "RH": 1}, "por_depto debería contar 3 en Ventas, 2 en TI y 1 en RH."
PY,
        ],
    ],
];
