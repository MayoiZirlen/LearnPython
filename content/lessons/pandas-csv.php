<?php
return [
    'title' => 'Leer y explorar un CSV',
    'icon' => '📂',
    'minutes' => 11,
    'summary' => 'Carga un archivo de 300 ventas reales y conócelo.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Los datos de verdad viven en archivos',
            'html' => <<<'HTML'
<p>Un <b>CSV</b> (valores separados por comas) es el formato más común para compartir datos. Es solo texto: la primera línea tiene los nombres de las columnas y cada línea siguiente es una fila.</p>
<pre>id,fecha,producto,categoria,region,vendedor,unidades,precio_unitario
1,2025-01-02,Silla,Muebles,Centro,Diego,19,132.08
2,2025-01-04,Monitor,Tecnología,Centro,Diego,2,231.76</pre>
<p>En este curso tienes listo el archivo <code>ventas.csv</code>: <b>300 ventas</b> de una tienda durante 2025. Leerlo es una sola línea:</p>
<pre>ventas = pd.read_csv("ventas.csv")</pre>
<div class="tip">💡 Puedes descargar <a href="data/ventas.csv" download>ventas.csv</a> y abrirlo en Excel para comparar.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Primer vistazo',
            'html' => '<p><code>head()</code> muestra las primeras 5 filas. Es lo primero que hace cualquier analista al abrir datos nuevos. Prueba también <code>tail()</code> o <code>sample(5)</code> (5 filas al azar).</p>',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")
print("Filas y columnas:", ventas.shape)
ventas.head()
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Si <code>ventas.shape</code> da <code>(300, 8)</code>, ¿qué significa?',
            'options' => ['300 columnas y 8 filas', '300 filas y 8 columnas', '300 ventas de 8 pesos', '8 archivos de 300 líneas'],
            'answer' => 1,
            'explain' => 'shape siempre es <b>(filas, columnas)</b>: 300 ventas, cada una con 8 datos.',
        ],
        [
            'type' => 'example',
            'title' => 'Radiografía de los datos',
            'html' => '<p><code>info()</code> muestra los tipos de cada columna y si hay datos faltantes. <code>describe()</code> da un resumen estadístico de las columnas numéricas: ¡todo lo que programaste a mano en una línea!</p>',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")
ventas.info()
print()
ventas.describe().round(2)
PY,
        ],
        [
            'type' => 'example',
            'title' => 'Conoce las categorías',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")

print("Productos distintos:", ventas["producto"].nunique())
print(ventas["producto"].unique())
print()
print("Ventas por región:")
print(ventas["region"].value_counts())
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📏 Tamaño del dataset',
            'html' => '<p>Lee <code>ventas.csv</code> y guarda en <code>num_filas</code> y <code>num_columnas</code> los valores de <code>shape</code>.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => ['<code>ventas.shape</code> es una pareja: <code>ventas.shape[0]</code> son las filas y <code>ventas.shape[1]</code> las columnas.', 'También puedes hacer <code>num_filas, num_columnas = ventas.shape</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\nnum_filas, num_columnas = ventas.shape\nprint(num_filas, num_columnas)\n",
            'check' => <<<'PY'
assert num_filas == 300, f"num_filas debería ser 300 y es {num_filas}."
assert num_columnas == 8, f"num_columnas debería ser 8 y es {num_columnas}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🔍 Detective de datos',
            'html' => <<<'HTML'
<p>Responde con código:</p>
<ul>
  <li><code>productos_unicos</code>: cuántos productos distintos hay (<code>nunique()</code>)</li>
  <li><code>region_top</code>: el <b>nombre</b> de la región con más ventas registradas. Pista: <code>value_counts()</code> ordena de mayor a menor, y <code>.idxmax()</code> devuelve la etiqueta del valor más alto.</li>
  <li><code>unidades_promedio</code>: el promedio de la columna <code>unidades</code></li>
</ul>
HTML,
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => ['<code>productos_unicos = ventas["producto"].nunique()</code>', '<code>region_top = ventas["region"].value_counts().idxmax()</code>', '<code>unidades_promedio = ventas["unidades"].mean()</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\nproductos_unicos = ventas[\"producto\"].nunique()\nregion_top = ventas[\"region\"].value_counts().idxmax()\nunidades_promedio = ventas[\"unidades\"].mean()\nprint(productos_unicos, region_top, unidades_promedio)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
assert productos_unicos == 10, f"Hay 10 productos distintos, obtuviste {productos_unicos}."
assert region_top == _v["region"].value_counts().idxmax(), f"La región con más ventas no es {region_top!r}. ¿Usaste value_counts()?"
assert abs(unidades_promedio - _v["unidades"].mean()) < 1e-9, "unidades_promedio debe ser la media de la columna unidades."
PY,
            'success' => 'Ya exploraste un dataset real como lo haría un analista profesional. 🕵️',
        ],
    ],
];
