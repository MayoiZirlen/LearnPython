<?php
return [
    'title' => 'Unir tablas (merge)',
    'icon' => '🔗',
    'minutes' => 13,
    'summary' => 'Combina hojas y tablas como un BUSCARV con superpoderes.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'Los datos viven separados',
            'html' => <<<'HTML'
<p>En la vida real, la información está repartida: las <b>ventas</b> en una tabla, las <b>metas</b> en otra, los <b>precios de lista</b> en otra… Para responder preguntas hay que <b>unirlas</b> usando una columna en común (una <b>clave</b>).</p>
<pre>ventas.merge(metas, on="vendedor", how="left")</pre>
<table>
<tr><th>how=</th><th>Qué filas conserva</th></tr>
<tr><td><code>"inner"</code></td><td>Solo las que tienen pareja en <b>ambas</b> tablas</td></tr>
<tr><td><code>"left"</code></td><td><b>Todas</b> las de la izquierda (las sin pareja quedan con NaN)</td></tr>
<tr><td><code>"right"</code></td><td>Todas las de la derecha</td></tr>
<tr><td><code>"outer"</code></td><td>Todas las de ambas</td></tr>
</table>
<div class="analogy">💘 Es como una fiesta de parejas: <code>inner</code> solo deja entrar a quienes llegaron con pareja; <code>left</code> deja entrar a todos los de tu lista, tengan pareja o no.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'inner vs. left',
            'code' => <<<'PY'
import pandas as pd

pedidos = pd.DataFrame({"cliente": ["Ana", "Luis", "Zoe", "Ana"], "monto": [100, 250, 80, 40]})
clientes = pd.DataFrame({"cliente": ["Ana", "Luis", "Carla"], "ciudad": ["Monterrey", "CDMX", "Cancún"]})

print("INNER (solo los que coinciden):")
print(pedidos.merge(clientes, on="cliente", how="inner"))
print("\nLEFT (todos los pedidos):")
pedidos.merge(clientes, on="cliente", how="left")
PY,
        ],
        [
            'type' => 'quiz',
            'question' => 'En el ejemplo, Zoe hizo un pedido pero no está en la tabla de clientes. Con <code>how="left"</code>, ¿qué pasa con su fila?',
            'options' => ['Desaparece', 'Aparece con la ciudad vacía (NaN)', 'Da error', 'Se le asigna la primera ciudad'],
            'answer' => 1,
            'explain' => 'Con <code>left</code> se conservan todas las filas de la izquierda. Las que no encuentran pareja quedan con <b>NaN</b> en las columnas de la otra tabla.',
        ],
        [
            'type' => 'exercise',
            'title' => '🎯 Cumplimiento de metas',
            'html' => <<<'HTML'
<p>El archivo <code>ventas.xlsx</code> tiene las hojas <code>Ventas</code> y <code>Metas</code>. Crea el DataFrame <code>reporte</code>:</p>
<ol>
  <li>Calcula el total de cada venta y súmalo por <code>vendedor</code> (con <code>as_index=False</code>)</li>
  <li>Une el resultado con las metas por <code>vendedor</code></li>
  <li>Agrega la columna <code>cumplimiento</code> = total / meta_anual × 100, redondeada a 1 decimal</li>
</ol>
HTML,
            'starter' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nmetas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Metas\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\n",
            'hint' => ['<code>por_vendedor = ventas.groupby("vendedor", as_index=False)["total"].sum()</code>', '<code>reporte = por_vendedor.merge(metas, on="vendedor")</code>', '<code>reporte["cumplimiento"] = (reporte["total"] / reporte["meta_anual"] * 100).round(1)</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nmetas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Metas\")\nventas[\"total\"] = ventas[\"unidades\"] * ventas[\"precio_unitario\"]\n\npor_vendedor = ventas.groupby(\"vendedor\", as_index=False)[\"total\"].sum()\nreporte = por_vendedor.merge(metas, on=\"vendedor\")\nreporte[\"cumplimiento\"] = (reporte[\"total\"] / reporte[\"meta_anual\"] * 100).round(1)\nreporte.sort_values(\"cumplimiento\", ascending=False)\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
_m = pd.read_excel("ventas.xlsx", sheet_name="Metas")
_v["total"] = _v["unidades"] * _v["precio_unitario"]
_r = _v.groupby("vendedor", as_index=False)["total"].sum().merge(_m, on="vendedor")
_r["c"] = (_r["total"] / _r["meta_anual"] * 100).round(1)
assert len(reporte) == 8, f"reporte debería tener 8 vendedores y tiene {len(reporte)}."
assert "cumplimiento" in reporte.columns and "meta_anual" in reporte.columns, "Faltan columnas: ¿uniste con metas y creaste cumplimiento?"
_u = reporte.set_index("vendedor")["cumplimiento"].sort_index()
assert ((_u - _r.set_index("vendedor")["c"].sort_index()).abs() < 0.051).all(), "Los porcentajes de cumplimiento no coinciden."
PY,
            'success' => '¡Unir tablas es de las habilidades más valiosas de un analista! 🔗',
        ],
        [
            'type' => 'exercise',
            'title' => '🏷️ ¿Cuánto descuento damos?',
            'html' => '<p>La hoja <code>Productos</code> tiene el <code>precio_lista</code> de cada producto. Une las ventas con esa hoja (clave: <code>producto</code>, y como ambas tienen <code>categoria</code>, usa <code>on=["producto", "categoria"]</code>). Crea la columna <code>descuento_pct</code> = (precio_lista − precio_unitario) / precio_lista × 100 y guarda en <code>desc_promedio</code> el promedio de esa columna.</p>',
            'starter' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nproductos = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Productos\")\n\ncon_lista = \n",
            'hint' => ['<code>con_lista = ventas.merge(productos, on=["producto", "categoria"], how="left")</code>', '<code>con_lista["descuento_pct"] = (con_lista["precio_lista"] - con_lista["precio_unitario"]) / con_lista["precio_lista"] * 100</code>'],
            'solution' => "import pandas as pd\n\nventas = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Ventas\")\nproductos = pd.read_excel(\"ventas.xlsx\", sheet_name=\"Productos\")\n\ncon_lista = ventas.merge(productos, on=[\"producto\", \"categoria\"], how=\"left\")\ncon_lista[\"descuento_pct\"] = (con_lista[\"precio_lista\"] - con_lista[\"precio_unitario\"]) / con_lista[\"precio_lista\"] * 100\ndesc_promedio = con_lista[\"descuento_pct\"].mean()\nprint(f\"Descuento promedio: {desc_promedio:.2f}%\")\ncon_lista.head()\n",
            'check' => <<<'PY'
import pandas as pd
_v = pd.read_excel("ventas.xlsx", sheet_name="Ventas").merge(pd.read_excel("ventas.xlsx", sheet_name="Productos"), on=["producto", "categoria"], how="left")
_d = ((_v["precio_lista"] - _v["precio_unitario"]) / _v["precio_lista"] * 100).mean()
assert len(con_lista) == 300, f"con_lista debería tener 300 filas (una por venta) y tiene {len(con_lista)}."
assert "precio_lista" in con_lista.columns, "No se trajo precio_lista: revisa el merge."
assert abs(desc_promedio - _d) < 1e-6, f"desc_promedio debería ser {_d:.4f}."
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Tip pro: revisa tus uniones',
            'html' => <<<'HTML'
<p>Un merge mal hecho puede <b>duplicar filas</b> sin avisar (si la clave se repite en ambas tablas). Dos trucos:</p>
<pre># indicator=True agrega la columna _merge: both / left_only / right_only
df.merge(otra, on="clave", how="outer", indicator=True)["_merge"].value_counts()

# validate lanza un error si la relación no es la esperada
ventas.merge(metas, on="vendedor", validate="many_to_one")</pre>
<p>Siempre compara <code>len()</code> antes y después de un merge. 🕵️</p>
HTML,
        ],
    ],
];
