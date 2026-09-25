<?php
return [
    'title' => 'Proyecto: analista de TiendaPy',
    'icon' => '🏆',
    'minutes' => 20,
    'summary' => 'Responde las preguntas del gerente con un análisis completo.',
    'steps' => [
        [
            'type' => 'text',
            'title' => '📨 Un correo del gerente',
            'html' => <<<'HTML'
<div class="analogy">
<p><b>De:</b> Gerente General de TiendaPy<br><b>Para:</b> Nuevo/a analista de datos (¡tú!)<br><b>Asunto:</b> Reporte anual 2025</p>
<p>¡Hola! Bienvenido/a al equipo. Mañana tengo junta con los directores y necesito respuestas claras:</p>
<ol>
  <li>¿Cuánto <b>ingresamos en total</b> en 2025?</li>
  <li>¿Cuál es nuestro <b>ticket promedio</b> (cuánto vale una venta en promedio)?</li>
  <li>¿Qué <b>categoría</b> nos deja más dinero?</li>
  <li>¿Cómo se comportaron las ventas <b>mes a mes</b>? Necesito un gráfico.</li>
  <li>¿Qué <b>región</b> vende más <b>tecnología</b>?</li>
</ol>
<p>Cuento contigo. 🙌</p>
</div>
<p>Tienes <code>ventas.csv</code> y todo lo que aprendiste. Cada reto es independiente, así que en cada uno cargamos los datos de nuevo. ¡Adelante!</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '1️⃣ Ingreso total',
            'html' => '<p>Carga los datos, crea la columna <code>total</code> y guarda en <code>ingreso_total</code> la suma de todas las ventas, <b>redondeada a 2 decimales</b>. Muéstralo con formato bonito, por ejemplo con <code>f"${ingreso_total:,.2f}"</code>.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\n\n",
            'hint' => ['<code>ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]</code>', '<code>ingreso_total = round(ventas["total"].sum(), 2)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\ningreso_total = round(ventas[\"total\"].sum(), 2)\nprint(f\"Ingreso total 2025: \${ingreso_total:,.2f}\")\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_t = round((_v["unidades"] * _v["precio_unitario"]).sum(), 2)
assert abs(ingreso_total - _t) < 0.01, f"El ingreso total debería ser {_t:,.2f}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '2️⃣ Ticket promedio',
            'html' => '<p>El <b>ticket promedio</b> es el valor medio de una venta. Guárdalo en <code>ticket_promedio</code> redondeado a 2 decimales. Como bonus, calcula también la <b>mediana</b> en <code>ticket_mediano</code> y compáralos: ¿por qué crees que son tan diferentes? 🤔</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>ticket_promedio = round(ventas["total"].mean(), 2)</code>', '<code>ticket_mediano = round(ventas["total"].median(), 2)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nticket_promedio = round(ventas[\"total\"].mean(), 2)\nticket_mediano = round(ventas[\"total\"].median(), 2)\nprint(\"Promedio:\", ticket_promedio)\nprint(\"Mediana:\", ticket_mediano)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_t = _v["unidades"] * _v["precio_unitario"]
assert abs(ticket_promedio - round(_t.mean(), 2)) < 0.01, "ticket_promedio debe ser la media de total."
assert abs(ticket_mediano - round(_t.median(), 2)) < 0.01, "ticket_mediano debe ser la mediana de total."
PY,
            'success' => 'Unas pocas ventas enormes (¡laptops!) jalan el promedio hacia arriba. La mediana muestra la venta "típica". ¡Recuerdas la lección de estadística! 🧠',
        ],
        [
            'type' => 'exercise',
            'title' => '3️⃣ Categoría estrella',
            'html' => '<p>Guarda en <code>categoria_top</code> el nombre de la categoría con más ingresos y en <code>porcentaje_top</code> qué porcentaje del ingreso total representa (redondeado a 1 decimal).</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>por_cat = ventas.groupby("categoria")["total"].sum()</code>', '<code>categoria_top = por_cat.idxmax()</code>', '<code>porcentaje_top = round(por_cat.max() / por_cat.sum() * 100, 1)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\npor_cat = ventas.groupby(\"categoria\")[\"total\"].sum()\ncategoria_top = por_cat.idxmax()\nporcentaje_top = round(por_cat.max() / por_cat.sum() * 100, 1)\nprint(f\"{categoria_top} aporta el {porcentaje_top}% de los ingresos\")\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_c = _v.groupby("categoria")["total"].sum()
assert categoria_top == _c.idxmax(), f"La categoría con más ingresos no es {categoria_top!r}."
assert abs(porcentaje_top - round(_c.max() / _c.sum() * 100, 1)) < 0.051, f"El porcentaje debería ser {round(_c.max() / _c.sum() * 100, 1)}."
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '4️⃣ Tendencia mensual (gráfico)',
            'html' => '<p>Crea <code>mensual</code>: la suma de <code>total</code> por número de mes (1 a 12). Después grafícala como <b>línea con marcadores</b>, con título y nombres en los ejes.</p>',
            'starter' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>ventas["mes"] = pd.to_datetime(ventas["fecha"]).dt.month</code>', '<code>mensual = ventas.groupby("mes")["total"].sum()</code>', '<code>mensual.plot(marker="o", title="Ventas mensuales 2025")</code>, luego <code>plt.xlabel</code>, <code>plt.ylabel</code> y <code>plt.show()</code>'],
            'solution' => "import pandas as pd\nimport matplotlib.pyplot as plt\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nventas[\"mes\"] = pd.to_datetime(ventas[\"fecha\"]).dt.month\nmensual = ventas.groupby(\"mes\")[\"total\"].sum()\n\nmensual.plot(marker=\"o\", figsize=(9, 4), title=\"Ventas mensuales 2025\")\nplt.xlabel(\"Mes\")\nplt.ylabel(\"Ingresos ($)\")\nplt.grid(alpha=0.3)\nplt.show()\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_m = _v.groupby(pd.to_datetime(_v["fecha"]).dt.month)["total"].sum()
assert len(mensual) == 12, "mensual debe tener 12 valores (uno por mes)."
assert all(abs(a - b) < 0.01 for a, b in zip(mensual.values, _m.values)), "Los totales mensuales no coinciden."
assert _imagenes >= 1, "Falta el gráfico."
assert "xlabel(" in _codigo and "ylabel(" in _codigo, "Ponle nombre a los ejes."
PY,
            'success' => '¡Ese gráfico va directo a la presentación de los directores! 📈',
        ],
        [
            'type' => 'exercise',
            'title' => '5️⃣ Tecnología por región',
            'html' => '<p>Filtra solo la categoría <code>"Tecnología"</code>, agrupa por región y suma el total. Guarda la Series ordenada de mayor a menor en <code>tec_region</code> y el nombre de la mejor región en <code>region_tec_top</code>.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['Primero filtra: <code>tec = ventas[ventas["categoria"] == "Tecnología"]</code>', 'Luego: <code>tec_region = tec.groupby("region")["total"].sum().sort_values(ascending=False)</code>', '<code>region_tec_top = tec_region.index[0]</code> (o <code>.idxmax()</code>)'],
            'solution' => "import pandas as pd\n\nventas = pd.read_csv(\"ventas.csv\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\ntec = ventas[ventas[\"categoria\"] == \"Tecnología\"]\ntec_region = tec.groupby(\"region\")[\"total\"].sum().sort_values(ascending=False)\nregion_tec_top = tec_region.idxmax()\nprint(tec_region.round(2))\nprint(\"Mejor región en tecnología:\", region_tec_top)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_csv("ventas.csv")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_e = _v[_v["categoria"] == "Tecnología"].groupby("region")["total"].sum().sort_values(ascending=False)
assert list(tec_region.index) == list(_e.index), "tec_region debe estar ordenada de mayor a menor (y contener solo Tecnología)."
assert ((tec_region - _e).abs() < 0.01).all(), "Los totales no coinciden. ¿Filtraste solo Tecnología?"
assert region_tec_top == _e.index[0], f"La mejor región en tecnología no es {region_tec_top!r}."
PY,
        ],
        [
            'type' => 'text',
            'title' => '🎓 ¡Eres analista de datos!',
            'html' => <<<'HTML'
<p>Acabas de hacer un análisis completo: cargaste datos, creaste indicadores, agrupaste, filtraste y graficaste. <b>Eso es exactamente lo que hace un analista de datos en su trabajo.</b> 👏</p>
<p><b>¿Qué sigue?</b></p>
<ul>
  <li>🧪 Usa el <a href="playground.php">Laboratorio</a> para hacer tus propias preguntas a <code>ventas.csv</code> y <code>clima.csv</code>.</li>
  <li>💻 Instala <b>Anaconda</b> o <b>Jupyter</b> en tu computadora para trabajar con tus propios archivos.</li>
  <li>📚 Aprende <b>seaborn</b> (gráficos más bonitos) y <b>SQL</b> (bases de datos).</li>
  <li>🏅 Busca datasets gratuitos en <b>Kaggle</b> o en portales de datos abiertos de tu país y practica.</li>
</ul>
<p>Pulsa <b>Terminar lección</b> para reclamar tu logro final. 🏆</p>
HTML,
        ],
    ],
];
