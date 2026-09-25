<?php
return [
    'title' => 'openpyxl: formato, fórmulas y colores',
    'icon' => '🎨',
    'minutes' => 14,
    'summary' => 'Controla cada celda: negritas, colores, fórmulas de Excel y anchos.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Cuando pandas no alcanza',
            'html' => <<<'HTML'
<p>pandas escribe <b>datos</b>, pero un reporte bonito necesita <b>formato</b>: encabezados de color, fórmulas que se recalculen, columnas anchas… Para eso está <b>openpyxl</b>, que trabaja celda por celda:</p>
<pre>from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill

libro = Workbook()          # un libro nuevo
hoja = libro.active         # su primera hoja
hoja.title = "Presupuesto"
hoja["A1"] = "Concepto"     # escribir en una celda
hoja.append(["Renta", 8000])            # agregar una fila completa
hoja["B5"] = "=SUM(B2:B4)"              # ¡una fórmula de Excel!
hoja["A1"].font = Font(bold=True)       # negritas
hoja["A1"].fill = PatternFill("solid", fgColor="FFD21F")   # fondo amarillo
hoja.column_dimensions["A"].width = 25  # ancho de columna
libro.save("presupuesto.xlsx")</pre>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Crear y volver a leer un libro',
            'html' => '<p>Con <code>load_workbook</code> puedes abrir un Excel existente y revisar o modificar sus celdas.</p>',
            'code' => <<<'PY'
from openpyxl import Workbook, load_workbook
from openpyxl.styles import Font, PatternFill

libro = Workbook()
hoja = libro.active
hoja.title = "Gastos"
hoja.append(["Concepto", "Monto"])
for fila in [["Renta", 8000], ["Comida", 3500], ["Internet", 600]]:
    hoja.append(fila)
hoja["A5"] = "TOTAL"
hoja["B5"] = "=SUM(B2:B4)"
for celda in hoja[1]:
    celda.font = Font(bold=True, color="FFFFFF")
    celda.fill = PatternFill("solid", fgColor="E5191C")
libro.save("gastos.xlsx")

# Volvemos a abrirlo
otra_vez = load_workbook("gastos.xlsx")["Gastos"]
for fila in otra_vez.iter_rows(values_only=True):
    print(fila)
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'Guardaste <code>=SUM(B2:B4)</code> con openpyxl. Al leer el archivo con <code>load_workbook(..., data_only=True)</code> la celda da <code>None</code>. ¿Por qué?',
            'options' => [
                'La fórmula está mal escrita',
                'openpyxl guarda la fórmula, pero quien la calcula es Excel al abrir el archivo',
                'openpyxl no acepta fórmulas',
                'Porque B5 está vacía',
            ],
            'answer' => 1,
            'explain' => 'openpyxl no es Excel: guarda el <b>texto</b> de la fórmula. El resultado aparece cuando Excel (o LibreOffice) abre y recalcula el libro. Si necesitas el número en Python, calcúlalo con pandas.',
        ],
        [
            'type' => 'exercise',
            'title' => '💰 Tu primer presupuesto',
            'html' => <<<'HTML'
<p>Crea el archivo <code>presupuesto.xlsx</code> con:</p>
<ul>
  <li>Fila 1: encabezados <code>Concepto</code> y <code>Monto</code>, en <b>negritas</b></li>
  <li>Filas 2 a 4: tres gastos cualesquiera (texto y número)</li>
  <li>Celda <code>A5</code> = <code>"TOTAL"</code> y <code>B5</code> = la fórmula <code>=SUM(B2:B4)</code></li>
</ul>
HTML,
            'starter' => "from openpyxl import Workbook\nfrom openpyxl.styles import Font\n\nlibro = Workbook()\nhoja = libro.active\n\n\nlibro.save(\"presupuesto.xlsx\")\nprint(\"Guardado ✅\")\n",
            'hint' => ['<code>hoja.append(["Concepto", "Monto"])</code> y luego tres <code>hoja.append([...])</code>.', 'Negritas: <code>for celda in hoja[1]: celda.font = Font(bold=True)</code>', 'Fórmula: <code>hoja["B5"] = "=SUM(B2:B4)"</code>'],
            'solution' => "from openpyxl import Workbook\nfrom openpyxl.styles import Font\n\nlibro = Workbook()\nhoja = libro.active\n\nhoja.append([\"Concepto\", \"Monto\"])\nhoja.append([\"Renta\", 8000])\nhoja.append([\"Comida\", 3500])\nhoja.append([\"Transporte\", 1200])\nhoja[\"A5\"] = \"TOTAL\"\nhoja[\"B5\"] = \"=SUM(B2:B4)\"\nfor celda in hoja[1]:\n    celda.font = Font(bold=True)\n\nlibro.save(\"presupuesto.xlsx\")\nprint(\"Guardado ✅\")\n",
            'check' => <<<'PY'
from openpyxl import load_workbook
_h = load_workbook("presupuesto.xlsx").active
assert _h["A1"].value == "Concepto" and _h["B1"].value == "Monto", "La fila 1 debe tener Concepto y Monto."
assert _h["A1"].font.bold and _h["B1"].font.bold, "Los encabezados deben ir en negritas."
for _f in range(2, 5):
    assert isinstance(_h.cell(_f, 1).value, str) and isinstance(_h.cell(_f, 2).value, (int, float)), f"La fila {_f} debe tener un concepto (texto) y un monto (número)."
assert _h["A5"].value == "TOTAL", "A5 debe decir TOTAL."
assert str(_h["B5"].value).replace(" ", "").upper() == "=SUM(B2:B4)", "B5 debe tener la fórmula =SUM(B2:B4)."
PY,
        ],
        [
            'type' => 'text',
            'title' => 'pandas + openpyxl: lo mejor de los dos',
            'html' => <<<'HTML'
<p>El flujo profesional: calculas con <b>pandas</b>, escribes con <code>ExcelWriter</code> y luego le das formato con <b>openpyxl</b> a la misma hoja:</p>
<pre>with pd.ExcelWriter("reporte.xlsx", engine="openpyxl") as excel:
    df.to_excel(excel, sheet_name="Ventas", index=False)
    hoja = excel.sheets["Ventas"]          # ← la hoja de openpyxl
    hoja.freeze_panes = "A2"               # inmovilizar encabezados
    for fila in hoja.iter_rows(min_row=2): # recorrer filas de datos
        ...</pre>
<p>Recuerda: en Excel las filas y columnas empiezan en <b>1</b>, y la fila 1 son los encabezados. 😉</p>
HTML,
        ],
        [
            'type' => 'exercise',
            'title' => '🚦 Resalta las ventas grandes',
            'html' => <<<'HTML'
<p>Escribe las ventas en <code>ventas_resaltadas.xlsx</code> (hoja <code>Ventas</code>) y pinta de rojo claro (<code>fgColor="FFC7CE"</code>) la celda de la columna <code>total</code> en cada fila donde el total sea <b>mayor a 3000</b>.</p>
<p>Pista: la columna <code>total</code> es la última, así que su número es <code>df.shape[1]</code>, y la fila de Excel de cada venta es su posición + 2.</p>
HTML,
            'starter' => "import pandas as pd\nfrom openpyxl.styles import PatternFill\n\ndf = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\ndf[\"total\"] = df[\"unidades\"] * df[\"precio_unitario\"]\nrojo = PatternFill(\"solid\", fgColor=\"FFC7CE\")\n\nwith pd.ExcelWriter(\"ventas_resaltadas.xlsx\", engine=\"openpyxl\") as excel:\n    df.to_excel(excel, sheet_name=\"Ventas\", index=False)\n    hoja = excel.sheets[\"Ventas\"]\n    col_total = df.shape[1]\n    # recorre los totales y pinta los que pasen de 3000\n\nprint(\"Listo ✅\")\n",
            'hint' => ['<code>for i, valor in enumerate(df["total"]):</code> te da la posición y el valor.', 'Dentro: <code>if valor > 3000: hoja.cell(row=i + 2, column=col_total).fill = rojo</code>'],
            'solution' => "import pandas as pd\nfrom openpyxl.styles import PatternFill\n\ndf = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\ndf[\"total\"] = df[\"unidades\"] * df[\"precio_unitario\"]\nrojo = PatternFill(\"solid\", fgColor=\"FFC7CE\")\n\nwith pd.ExcelWriter(\"ventas_resaltadas.xlsx\", engine=\"openpyxl\") as excel:\n    df.to_excel(excel, sheet_name=\"Ventas\", index=False)\n    hoja = excel.sheets[\"Ventas\"]\n    col_total = df.shape[1]\n    for i, valor in enumerate(df[\"total\"]):\n        if valor > 3000:\n            hoja.cell(row=i + 2, column=col_total).fill = rojo\n\nprint(\"Listo ✅\")\n",
            'check' => <<<'PY'
import pandas as pd
from openpyxl import load_workbook
_v = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
_esperadas = int(((_v["unidades"] * _v["precio_unitario"]) > 3000).sum())
_h = load_workbook("ventas_resaltadas.xlsx")["Ventas"]
_col = _h.max_column
_pintadas = [c.row for c in _h.iter_cols(min_col=_col, max_col=_col, min_row=2).__next__() if c.fill and c.fill.fgColor and str(c.fill.fgColor.rgb).upper().endswith("FFC7CE")]
assert _h.cell(1, _col).value == "total", "La última columna debe ser total."
assert len(_pintadas) == _esperadas, f"Deberías pintar {_esperadas} celdas y pintaste {len(_pintadas)}."
for _f in _pintadas:
    assert _h.cell(_f, _col).value > 3000, f"La fila {_f} está pintada pero su total no pasa de 3000. ¿Usaste i + 2?"
PY,
            'success' => '¡Formato condicional hecho con código! Imagina aplicarlo a 50 reportes en un segundo. 🚦',
        ],
    ],
];
