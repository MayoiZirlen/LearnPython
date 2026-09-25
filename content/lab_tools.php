<?php
// Caja de herramientas del laboratorio: fragmentos listos para analizar archivos (Excel/CSV).
// Todos usan la variable `archivo`, así el estudiante solo cambia el nombre por el de su archivo.
return [
    '📥 Leer datos' => [
        'Leer Excel (todas las hojas)' => <<<'PY'
import pandas as pd

archivo = "ventas.xlsx"   # 👈 cambia por el nombre de tu archivo

hojas = pd.read_excel(archivo, sheet_name=None)   # None = todas las hojas
for nombre, tabla in hojas.items():
    print(f"📄 Hoja '{nombre}': {tabla.shape[0]} filas × {tabla.shape[1]} columnas")
    print("   Columnas:", list(tabla.columns))
PY,
        'Leer una hoja' => <<<'PY'
import pandas as pd

archivo = "ventas.xlsx"
df = pd.read_excel(archivo, sheet_name="Ventas")   # nombre o número de la hoja (0 = primera)

# Opciones útiles si tu Excel tiene títulos arriba o columnas de más:
# pd.read_excel(archivo, skiprows=3)            salta las primeras 3 filas
# pd.read_excel(archivo, usecols="A:E")          solo las columnas A a E
# pd.read_excel(archivo, header=None)            si no tiene encabezados
df.head(10)
PY,
        'Leer CSV' => <<<'PY'
import pandas as pd

archivo = "ventas.csv"
# Si tu CSV usa punto y coma o acentos raros, prueba: sep=";", encoding="latin-1"
df = pd.read_csv(archivo)
df.head(10)
PY,
    ],
    '🔍 Explorar' => [
        'Radiografía completa' => <<<'PY'
import pandas as pd

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")

print("📐 Tamaño:", df.shape[0], "filas ×", df.shape[1], "columnas\n")
print("🧬 Tipos de datos:")
print(df.dtypes, "\n")
print("🕳️ Datos faltantes por columna:")
print(df.isna().sum(), "\n")
print("👯 Filas duplicadas:", df.duplicated().sum(), "\n")
df.describe(include="all").round(2)
PY,
        'Valores más frecuentes' => <<<'PY'
import pandas as pd

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
columna = "producto"   # 👈 elige una columna de texto

conteo = df[columna].value_counts()
porcentaje = (df[columna].value_counts(normalize=True) * 100).round(1)
pd.DataFrame({"veces": conteo, "%": porcentaje})
PY,
    ],
    '🧮 Analizar' => [
        'Tabla dinámica' => <<<'PY'
import pandas as pd

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]
df["mes"] = pd.to_datetime(df["fecha"]).dt.month

# Igual que una tabla dinámica de Excel: filas, columnas, valores y operación
pd.pivot_table(df, index="mes", columns="region", values="total",
               aggfunc="sum", margins=True, margins_name="TOTAL").round(0)
PY,
        'Unir hojas (ventas vs metas)' => <<<'PY'
import pandas as pd

ventas = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
metas = pd.read_excel("ventas.xlsx", sheet_name="Metas")
ventas["total"] = ventas["unidades"] * ventas["precio_unitario"]

# Sumamos por vendedor y lo unimos con sus metas (como un BUSCARV de Excel)
real = ventas.groupby("vendedor", as_index=False)["total"].sum()
reporte = metas.merge(real, on="vendedor", how="left")
reporte["avance_%"] = (reporte["total"] / reporte["meta_anual"] * 100).round(1)
reporte["¿cumplió?"] = reporte["avance_%"].apply(lambda x: "✅" if x >= 100 else "❌")
reporte.sort_values("avance_%", ascending=False)
PY,
        'Correlación entre columnas' => <<<'PY'
import pandas as pd

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]

# 1 = se mueven juntas, -1 = en sentido contrario, 0 = sin relación
df[["unidades", "precio_unitario", "total"]].corr().round(2)
PY,
    ],
    '📈 Graficar' => [
        'Barras por categoría' => <<<'PY'
import pandas as pd
import matplotlib.pyplot as plt

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]

resumen = df.groupby("categoria")["total"].sum().sort_values()
resumen.plot(kind="barh", color="#e5191c", figsize=(8, 4), title="Ingresos por categoría")
plt.xlabel("Ingresos ($)")
plt.show()
PY,
        'Mapa de calor (seaborn)' => <<<'PY'
import pandas as pd
import seaborn as sns
import matplotlib.pyplot as plt

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]
df["mes"] = pd.to_datetime(df["fecha"]).dt.month

tabla = df.pivot_table(index="region", columns="mes", values="total", aggfunc="sum")
plt.figure(figsize=(10, 3.5))
sns.heatmap(tabla, cmap="Reds", annot=False, linewidths=.5)
plt.title("Ventas por región y mes")
plt.show()
PY,
    ],
    '🤖 Estadística y modelos' => [
        'Regresión lineal (scikit-learn)' => <<<'PY'
import pandas as pd
from sklearn.linear_model import LinearRegression

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]
df["mes"] = pd.to_datetime(df["fecha"]).dt.month
mensual = df.groupby("mes", as_index=False)["total"].sum()

modelo = LinearRegression()
modelo.fit(mensual[["mes"]], mensual["total"])
print(f"Tendencia: {modelo.coef_[0]:+,.2f} $ por mes")
print(f"R² (qué tan bien ajusta, de 0 a 1): {modelo.score(mensual[['mes']], mensual['total']):.2f}")

futuro = pd.DataFrame({"mes": [13, 14, 15]})
futuro["pronostico"] = modelo.predict(futuro[["mes"]]).round(2)
futuro
PY,
        'Comparar dos grupos (scipy)' => <<<'PY'
import pandas as pd
from scipy import stats

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]

norte = df[df["region"] == "Norte"]["total"]
sur = df[df["region"] == "Sur"]["total"]
resultado = stats.ttest_ind(norte, sur, equal_var=False)

print(f"Promedio Norte: {norte.mean():,.2f}  |  Promedio Sur: {sur.mean():,.2f}")
print(f"Valor p: {resultado.pvalue:.3f}")
if resultado.pvalue < 0.05:
    print("➡️ La diferencia es estadísticamente significativa.")
else:
    print("➡️ La diferencia podría deberse al azar.")
PY,
    ],
    '📤 Exportar' => [
        'Exportar reporte a Excel' => <<<'PY'
import pandas as pd

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
df["total"] = df["unidades"] * df["precio_unitario"]

por_region = df.groupby("region", as_index=False)["total"].sum().round(2)
por_producto = df.groupby("producto", as_index=False)["total"].sum().round(2)

# Un archivo con varias hojas. Aparecerá en "Mis archivos" para descargarlo ⬇️
with pd.ExcelWriter("reporte.xlsx") as excel:
    por_region.to_excel(excel, sheet_name="Por región", index=False)
    por_producto.to_excel(excel, sheet_name="Por producto", index=False)
    df.to_excel(excel, sheet_name="Detalle", index=False)

print("✅ Listo: descarga reporte.xlsx desde el panel «Mis archivos»")
PY,
        'Exportar a CSV' => <<<'PY'
import pandas as pd

df = pd.read_excel("ventas.xlsx", sheet_name="Ventas")
grandes = df[df["unidades"] >= 10]
grandes.to_csv("ventas_grandes.csv", index=False)
print(f"✅ Guardé {len(grandes)} filas en ventas_grandes.csv")
PY,
    ],
];
