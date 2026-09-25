<?php
return [
    'title' => 'lambda, apply y map',
    'icon' => '⚙️',
    'minutes' => 11,
    'summary' => 'Funciones exprés para ordenar y transformar columnas enteras.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Funciones de una sola línea',
            'html' => <<<'HTML'
<p>Una <code>lambda</code> es una función sin nombre, de una sola línea. Estas dos son equivalentes:</p>
<pre>def doble(x):
    return x * 2

doble = lambda x: x * 2</pre>
<p>Brillan cuando las pasas a otra función. Por ejemplo, <code>sorted</code> acepta un <code>key</code> que dice <b>por qué ordenar</b>:</p>
<pre>productos = [("Laptop", 850), ("Mouse", 25), ("Monitor", 220)]
sorted(productos, key=lambda p: p[1])   # ordena por precio</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Ordenar como quieras',
            'code' => <<<'PY'
vendedores = [
    {"nombre": "Ana", "ventas": 25400, "region": "Norte"},
    {"nombre": "Luis", "ventas": 17900, "region": "Norte"},
    {"nombre": "Carla", "ventas": 23400, "region": "Sur"},
]

por_ventas = sorted(vendedores, key=lambda v: v["ventas"], reverse=True)
print([v["nombre"] for v in por_ventas])

por_nombre_largo = sorted(vendedores, key=lambda v: len(v["nombre"]))
print([v["nombre"] for v in por_nombre_largo])

mejor = max(vendedores, key=lambda v: v["ventas"])
print("Mejor:", mejor["nombre"])
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '💎 Ordena el inventario',
            'html' => '<p>Crea <code>ordenados</code> con los productos ordenados por su <b>valor en inventario</b> (precio × stock), de mayor a menor. Usa <code>sorted</code> con <code>key=lambda …</code> y <code>reverse=True</code>.</p>',
            'starter' => "productos = [\n    {\"nombre\": \"Laptop\", \"precio\": 850, \"stock\": 5},\n    {\"nombre\": \"Mouse\", \"precio\": 25, \"stock\": 200},\n    {\"nombre\": \"Monitor\", \"precio\": 220, \"stock\": 30},\n    {\"nombre\": \"Silla\", \"precio\": 130, \"stock\": 12},\n]\n\nordenados = \nprint([p[\"nombre\"] for p in ordenados])\n",
            'hint' => '<code>sorted(productos, key=lambda p: p["precio"] * p["stock"], reverse=True)</code>',
            'solution' => "productos = [\n    {\"nombre\": \"Laptop\", \"precio\": 850, \"stock\": 5},\n    {\"nombre\": \"Mouse\", \"precio\": 25, \"stock\": 200},\n    {\"nombre\": \"Monitor\", \"precio\": 220, \"stock\": 30},\n    {\"nombre\": \"Silla\", \"precio\": 130, \"stock\": 12},\n]\n\nordenados = sorted(productos, key=lambda p: p[\"precio\"] * p[\"stock\"], reverse=True)\nprint([p[\"nombre\"] for p in ordenados])\n",
            'check' => <<<'PY'
assert "lambda" in _codigo, "Usa una lambda en key=."
assert [p["nombre"] for p in ordenados] == ["Monitor", "Mouse", "Laptop", "Silla"], f"El orden quedó así: {[p['nombre'] for p in ordenados]}"
PY,
        ],
        [
            'type' => 'text',
            'title' => 'apply y map en pandas',
            'html' => <<<'HTML'
<p>En pandas, cuando necesitas una transformación que no existe como operación directa, usa:</p>
<ul>
  <li><code>serie.apply(funcion)</code> → aplica la función a <b>cada valor</b> de una columna.</li>
  <li><code>serie.map(diccionario)</code> → <b>traduce</b> cada valor usando un diccionario (como un BUSCARV rápido).</li>
</ul>
<pre>ventas["tamaño"] = ventas["unidades"].apply(lambda u: "grande" if u >= 10 else "chico")
ventas["zona"] = ventas["region"].map({"Norte": "Z1", "Sur": "Z2"})</pre>
<div class="warn">⚠️ <code>apply</code> es más lento que las operaciones vectorizadas (<code>df["a"] * 2</code>). Úsalo cuando no haya otra opción.</div>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '📦 ¿Pedido grande o chico?',
            'html' => '<p>En <code>ventas.csv</code>, crea la columna <code>tamaño</code> con <code>"grande"</code> si las unidades son 10 o más, y <code>"chico"</code> si no. Usa <code>apply</code> con una lambda. Después guarda en <code>conteo</code> el <code>value_counts()</code> de esa columna.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => '<code>ventas["tamaño"] = ventas["unidades"].apply(lambda u: "grande" if u >= 10 else "chico")</code>',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\nventas[\"tamaño\"] = ventas[\"unidades\"].apply(lambda u: \"grande\" if u >= 10 else \"chico\")\nconteo = ventas[\"tamaño\"].value_counts()\nconteo\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
assert "tamaño" in ventas.columns, "Crea la columna tamaño (con ñ)."
assert int(conteo["grande"]) == int((_v["unidades"] >= 10).sum()), "El conteo de 'grande' no coincide. ¿Usaste >= 10?"
assert int(conteo["chico"]) == int((_v["unidades"] < 10).sum()), "El conteo de 'chico' no coincide."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🗺️ Traduce regiones a zonas',
            'html' => '<p>La empresa agrupa las regiones en zonas: Norte y Este son <code>"Zona A"</code>; Sur y Centro son <code>"Zona B"</code>. Crea la columna <code>zona</code> con <code>map</code> y calcula <code>ventas_zona</code>: la suma de <code>unidades</code> por zona.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nzonas = {\"Norte\": \"Zona A\", \"Este\": \"Zona A\", \"Sur\": \"Zona B\", \"Centro\": \"Zona B\"}\n\n",
            'hint' => ['<code>ventas["zona"] = ventas["region"].map(zonas)</code>', '<code>ventas_zona = ventas.groupby("zona")["unidades"].sum()</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nzonas = {\"Norte\": \"Zona A\", \"Este\": \"Zona A\", \"Sur\": \"Zona B\", \"Centro\": \"Zona B\"}\n\nventas[\"zona\"] = ventas[\"region\"].map(zonas)\nventas_zona = ventas.groupby(\"zona\")[\"unidades\"].sum()\nventas_zona\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
assert ventas["zona"].isna().sum() == 0, "Hay filas sin zona. ¿Usaste map(zonas)?"
_a = int(_v[_v["region"].isin(["Norte", "Este"])]["unidades"].sum())
assert int(ventas_zona["Zona A"]) == _a, f"La Zona A debería sumar {_a} unidades."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué hace <code>df["pais"].map({"MX": "México", "AR": "Argentina"})</code> con un valor <code>"CL"</code>?',
            'options' => ['Lo deja como "CL"', 'Lo convierte en NaN (vacío)', 'Da error', 'Lo convierte en "Chile"'],
            'answer' => 1,
            'explain' => 'Si un valor no está en el diccionario, <code>map</code> lo convierte en <b>NaN</b>. ¡Revisa siempre con <code>.isna().sum()</code> después de un map!',
        ],
    ],
];
