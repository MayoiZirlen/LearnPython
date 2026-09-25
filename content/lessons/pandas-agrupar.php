<?php
return [
    'title' => 'Agrupar y resumir',
    'icon' => '🧺',
    'minutes' => 12,
    'summary' => 'groupby: responde "¿cuánto vendimos por región?" en una línea.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Separar, aplicar, combinar',
            'html' => <<<'HTML'
<p>Las preguntas de negocio suelen ser del tipo <b>"¿cuánto… por…?"</b>: ventas <i>por región</i>, clientes <i>por ciudad</i>, promedio <i>por vendedor</i>… Para eso existe <code>groupby</code>.</p>
<div class="analogy">🍬 Imagina una bolsa de dulces de colores. Para saber cuántos hay de cada color: <b>1)</b> los <b>separas</b> por color, <b>2)</b> <b>cuentas</b> cada montón, <b>3)</b> <b>juntas</b> los resultados en una lista. ¡Eso es groupby!</div>
<pre>ventas.groupby("region")["total"].sum()
#       ↑ separa por     ↑ esta columna  ↑ aplica esta operación</pre>
<p>Puedes usar cualquier operación: <code>sum()</code>, <code>mean()</code>, <code>count()</code>, <code>max()</code>, <code>min()</code>, <code>median()</code>…</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Ventas por región',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]

por_region = ventas.groupby("region")["total"].sum().sort_values(ascending=False)
print(por_region.round(2))
print()
print("Ticket promedio por región:")
print(ventas.groupby("region")["total"].mean().round(2))
PY,
        ],
        [
            'type' => 'example',
            'title' => 'Varias estadísticas a la vez',
            'html' => '<p>Con <code>agg()</code> calculas varias cosas al mismo tiempo, y agrupar por <b>dos</b> columnas crea combinaciones:</p>',
            'code' => <<<'PY'
import pandas as pd

ventas = pd.read_csv("ventas.csv")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]

resumen = ventas.groupby("categoria")["total"].agg(["count", "sum", "mean", "max"]).round(2)
print(resumen)
print()

# Tabla dinámica (pivot table), como en Excel
ventas.pivot_table(index="region", columns="categoria", values="total", aggfunc="sum").round(0)
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué responde <code>ventas.groupby("vendedor")["unidades"].mean()</code>?',
            'options' => [
                'El total de unidades vendidas',
                'El promedio de unidades por venta de cada vendedor',
                'Cuántos vendedores hay',
                'El vendedor con más unidades',
            ],
            'answer' => 1,
            'explain' => 'Separa por vendedor, toma la columna unidades y calcula el promedio en cada grupo.',
        ],
        [
            'type' => 'exercise',
            'title' => '🗂️ Ingresos por categoría',
            'html' => '<p>Crea la columna <code>total</code> y luego la Series <code>por_categoria</code> con la <b>suma</b> de <code>total</code> para cada <code>categoria</code>, ordenada de mayor a menor.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>ventas.groupby("categoria")["total"].sum()</code>', 'Añade al final <code>.sort_values(ascending=False)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\npor_categoria = ventas.groupby(\"categoria\")[\"total\"].sum().sort_values(ascending=False)\npor_categoria\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_e = _v.groupby("categoria")["total"].sum().sort_values(ascending=False)
assert isinstance(por_categoria, pd.Series), "por_categoria debe ser una Series (resultado de groupby + sum)."
assert list(por_categoria.index) == list(_e.index), "Revisa el orden: debe ir de mayor a menor."
assert ((por_categoria - _e).abs() < 0.01).all(), "Los totales no coinciden. ¿Usaste sum()?"
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '🥇 Vendedor estrella',
            'html' => '<p>Encuentra el <b>nombre</b> del vendedor con más ingresos (suma de <code>total</code>) y guárdalo en <code>mejor_vendedor</code>. Pista: <code>.idxmax()</code> devuelve la etiqueta del valor más alto.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => '<code>mejor_vendedor = ventas.groupby("vendedor")["total"].sum().idxmax()</code>',
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nmejor_vendedor = ventas.groupby(\"vendedor\")[\"total\"].sum().idxmax()\nprint(\"🏆\", mejor_vendedor)\n",
            'check' => <<<'PY'
assert mejor_vendedor == "Sofía", f"{mejor_vendedor!r} no es quien más vendió. Agrupa por vendedor y suma el total."
PY,
            'success' => '¡Felicidades a Sofía! 🎉 Y a ti por encontrarla.',
        ],
        [
            'type' => 'text',
            'title' => 'Trabajar con fechas',
            'html' => <<<'HTML'
<p>La columna <code>fecha</code> se lee como texto. Para analizar por mes o día hay que convertirla a fecha real con <code>pd.to_datetime</code>. Después, <code>.dt</code> te da acceso a sus partes:</p>
<pre>ventas["fecha"] = pd.to_datetime(ventas["fecha"])
ventas["mes"] = ventas["fecha"].dt.month        # 1 a 12
ventas["dia_semana"] = ventas["fecha"].dt.day_name()   # Monday, Tuesday…</pre>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '📅 El mejor mes',
            'html' => '<p>Convierte la fecha, crea la columna <code>mes</code> y calcula <code>ventas_mensuales</code> (suma de total por mes). Luego guarda en <code>mes_top</code> el <b>número</b> del mes con más ventas.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>ventas["mes"] = pd.to_datetime(ventas["fecha"]).dt.month</code>', '<code>ventas_mensuales = ventas.groupby("mes")["total"].sum()</code> y luego <code>.idxmax()</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas[\"mes\"] = pd.to_datetime(ventas[\"fecha\"]).dt.month\nventas_mensuales = ventas.groupby(\"mes\")[\"total\"].sum()\nmes_top = ventas_mensuales.idxmax()\nprint(ventas_mensuales.round(2))\nprint(\"Mejor mes:\", mes_top)\n",
            'check' => <<<'PY'
assert "mes" in ventas.columns, "Crea la columna mes."
assert len(ventas_mensuales) == 12, "ventas_mensuales debería tener un valor por cada uno de los 12 meses."
assert mes_top == 8, f"El mes con más ventas no es {mes_top}. Usa idxmax() sobre ventas_mensuales."
PY,
        ],
    ],
];
