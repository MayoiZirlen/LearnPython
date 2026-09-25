<?php
return [
    'title' => 'Excel con pandas: libros de varias hojas',
    'icon' => '📗',
    'minutes' => 12,
    'summary' => 'Lee todas las hojas, elige rangos y genera libros con una hoja por región.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Leer Excel como un profesional',
            'html' => <<<'HTML'
<p><code>pd.read_excel</code> tiene opciones para casi cualquier Excel "difícil":</p>
<table>
<tr><th>Opción</th><th>Para qué</th></tr>
<tr><td><code>sheet_name="Metas"</code></td><td>Leer una hoja por nombre (o número: 0 = primera)</td></tr>
<tr><td><code>sheet_name=None</code></td><td>Leer <b>todas</b> las hojas → un diccionario {nombre: tabla}</td></tr>
<tr><td><code>skiprows=3</code></td><td>Saltar filas de título arriba de la tabla</td></tr>
<tr><td><code>usecols="A:D"</code></td><td>Solo algunas columnas</td></tr>
<tr><td><code>nrows=100</code></td><td>Solo las primeras filas (para archivos enormes)</td></tr>
</table>
<p>Y para escribir varias hojas en el mismo archivo se usa <code>pd.ExcelWriter</code>:</p>
<pre>with pd.ExcelWriter("reporte.xlsx") as excel:
    tabla1.to_excel(excel, sheet_name="Resumen", index=False)
    tabla2.to_excel(excel, sheet_name="Detalle", index=False)</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Explorar un libro completo',
            'code' => <<<'PY'
import pandas as pd

hojas = pd.read_excel("ventas.xlsx", sheet_name=None)
print("Hojas:", list(hojas.keys()))
for nombre, tabla in hojas.items():
    print(f"  📄 {nombre}: {tabla.shape[0]} filas, columnas {list(tabla.columns)}")

hojas["Metas"]
PY,
        ],
        [
            'type' => 'exercise',
            'title' => '📚 Inventario del libro',
            'html' => '<p>Lee <b>todas</b> las hojas de <code>ventas.xlsx</code> en <code>hojas</code>. Guarda en <code>n_hojas</code> cuántas hojas tiene y en <code>total_filas</code> la suma de filas de todas las hojas.</p>',
            'starter' => "import pandas as pd\n\n",
            'hint' => ['<code>hojas = pd.read_excel("ventas.xlsx", sheet_name=None)</code>', '<code>total_filas = sum(len(t) for t in hojas.values())</code>'],
            'solution' => "import pandas as pd\n\nhojas = pd.read_excel(\"ventas.xlsx\", sheet_name=None)\nn_hojas = len(hojas)\ntotal_filas = sum(len(t) for t in hojas.values())\nprint(n_hojas, total_filas)\n",
            'check' => <<<'PY'
assert isinstance(hojas, dict), "hojas debe ser un diccionario: usa sheet_name=None."
assert n_hojas == 3, f"ventas.xlsx tiene 3 hojas, obtuviste {n_hojas}."
assert total_filas == 318, f"En total hay 318 filas (300 + 8 + 10), obtuviste {total_filas}."
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Por qué casi siempre se usa <code>index=False</code> en <code>to_excel</code>?',
            'options' => [
                'Para que el archivo pese menos de la mitad',
                'Para no escribir la columna extra con los números de fila 0, 1, 2…',
                'Porque sin eso no se puede abrir en Excel',
                'Para ordenar las filas',
            ],
            'answer' => 1,
            'explain' => 'El índice de pandas (0, 1, 2…) normalmente no significa nada para quien abre el Excel, así que se omite.',
        ],
        [
            'type' => 'exercise',
            'title' => '🗂️ Una hoja por región',
            'html' => <<<'HTML'
<p>El gerente quiere un Excel con <b>una hoja por región</b>, cada una con las ventas de esa región. Crea <code>reporte_regiones.xlsx</code>:</p>
<ul>
  <li>Recorre <code>ventas.groupby("region")</code> (cada vuelta te da <code>nombre, grupo</code>)</li>
  <li>Escribe cada <code>grupo</code> en una hoja llamada como la región, sin índice</li>
</ul>
HTML,
            'starter' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\n\nwith pd.ExcelWriter(\"reporte_regiones.xlsx\") as excel:\n    # tu bucle aquí\n    pass\n\nprint(\"Listo ✅\")\n",
            'hint' => ['<code>for region, grupo in ventas.groupby("region"):</code>', 'Dentro del for: <code>grupo.to_excel(excel, sheet_name=region, index=False)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\n\nwith pd.ExcelWriter(\"reporte_regiones.xlsx\") as excel:\n    for region, grupo in ventas.groupby(\"region\"):\n        grupo.to_excel(excel, sheet_name=region, index=False)\n\nprint(\"Listo ✅\")\n",
            'check' => <<<'PY'
import pandas as pd
_l = pd.read_excel("reporte_regiones.xlsx", sheet_name=None)
_v = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
assert sorted(_l) == ["Centro", "Este", "Norte", "Sur"], f"Las hojas del archivo son {list(_l)}; deben ser las 4 regiones."
for _r, _t in _l.items():
    assert len(_t) == int((_v["region"] == _r).sum()), f"La hoja {_r} no tiene las filas correctas."
    assert "Unnamed: 0" not in _t.columns, "Se escribió el índice: usa index=False."
PY,
            'success' => '¡Generaste un reporte de 4 hojas automáticamente! Descárgalo desde el Laboratorio si quieres verlo en Excel. 📗',
        ],
        [
            'type' => 'exercise',
            'title' => '📑 Resumen + detalle',
            'html' => '<p>Crea <code>resumen_ventas.xlsx</code> con dos hojas: <code>Resumen</code> (total por categoría, con columnas <code>categoria</code> y <code>total</code>) y <code>Detalle</code> (todas las ventas con su columna <code>total</code>).</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>resumen = ventas.groupby("categoria", as_index=False)["total"].sum()</code>', 'Dos <code>to_excel</code> dentro del mismo <code>with pd.ExcelWriter("resumen_ventas.xlsx") as excel:</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\nresumen = ventas.groupby(\"categoria\", as_index=False)[\"total\"].sum()\nwith pd.ExcelWriter(\"resumen_ventas.xlsx\") as excel:\n    resumen.to_excel(excel, sheet_name=\"Resumen\", index=False)\n    ventas.to_excel(excel, sheet_name=\"Detalle\", index=False)\nprint(\"Listo ✅\")\n",
            'check' => <<<'PY'
import pandas as pd
assert "resumen_ventas.xlsx" in _codigo and "ExcelWriter" in _codigo, "Escribe el archivo resumen_ventas.xlsx con pd.ExcelWriter."
_l = pd.read_excel("resumen_ventas.xlsx", sheet_name=None)
assert list(_l) == ["Resumen", "Detalle"], f"Las hojas deben ser Resumen y Detalle (en ese orden); son {list(_l)}."
assert list(_l["Resumen"].columns) == ["categoria", "total"], f"Resumen debe tener columnas categoria y total; tiene {list(_l['Resumen'].columns)}."
assert len(_l["Resumen"]) == 3 and len(_l["Detalle"]) == 300, "Revisa el contenido de las hojas."
assert "total" in _l["Detalle"].columns, "El detalle debe incluir la columna total."
PY,
        ],
    ],
];
