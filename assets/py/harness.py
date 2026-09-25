"""
Arnés de ejecución de PyAprende.

Este archivo lo carga el Web Worker dentro de Pyodide (Python en el navegador)
y también lo usa tools/test_exercises.py para probar las lecciones con Python normal.

Ejecuta el código del estudiante, captura lo que imprime, los gráficos de
matplotlib y el valor de la última expresión (como en Jupyter), traduce los
errores a pistas en español y, si hay un "check", valida el ejercicio.
"""
import ast
import base64
import builtins
import io
import json
import os
import re
import sys
import traceback

ARCHIVO = "<tu código>"
os.environ.setdefault("MPLBACKEND", "agg")

PISTAS = {
    "NameError": "Python no conoce ese nombre. ¿Lo escribiste igual que cuando lo creaste? "
                 "Recuerda que mayúsculas y minúsculas cuentan, y que los textos van entre comillas.",
    "SyntaxError": "Hay algo mal escrito. Revisa paréntesis, comillas y los dos puntos (:) "
                   "al final de if, for, while y def.",
    "IndentationError": "La sangría (los espacios al inicio de la línea) no cuadra. "
                        "Después de una línea que termina en ':' el bloque va con 4 espacios.",
    "TabError": "Mezclaste tabuladores y espacios. Usa solo espacios (4 por nivel).",
    "TypeError": "Estás mezclando tipos que no combinan, por ejemplo texto + número. "
                 "Prueba convertir con str(), int() o float().",
    "ZeroDivisionError": "¡Dividiste entre cero! Ni Python puede hacer eso.",
    "IndexError": "Pediste una posición que no existe. Recuerda que las posiciones empiezan en 0 "
                  "y la última es len(lista) - 1.",
    "KeyError": "Esa clave no existe en el diccionario (o esa columna no existe en la tabla). "
                "Revisa cómo está escrita.",
    "ValueError": "El tipo es correcto pero el valor no sirve, por ejemplo int('hola').",
    "AttributeError": "Ese objeto no tiene ese método o atributo. ¿Está bien escrito el nombre?",
    "ModuleNotFoundError": "Esa librería no está disponible aquí. Puedes usar numpy, pandas y matplotlib.",
    "ImportError": "No se pudo importar eso. Revisa el nombre.",
    "UnboundLocalError": "Usaste una variable dentro de una función antes de darle valor.",
    "RecursionError": "Una función se llamó a sí misma demasiadas veces.",
}


class _Entradas:
    """Reemplazo de input(): usa respuestas predefinidas porque no hay teclado dentro del worker."""

    def __init__(self, valores):
        self.valores = list(valores or [])

    def __call__(self, mensaje=""):
        print(mensaje, end="")
        if self.valores:
            valor = str(self.valores.pop(0))
        else:
            valor = ""
            print("\n[input() no recibió respuesta: agrega valores en «Entradas»]", end="")
        print(valor)
        return valor


def _figuras_a_png():
    if "matplotlib.pyplot" not in sys.modules:
        return []
    plt = sys.modules["matplotlib.pyplot"]
    imagenes = []
    for num in plt.get_fignums():
        fig = plt.figure(num)
        buf = io.BytesIO()
        fig.savefig(buf, format="png", bbox_inches="tight", dpi=100)
        imagenes.append(base64.b64encode(buf.getvalue()).decode("ascii"))
    plt.close("all")
    return imagenes


_USA_GRAFICOS = re.compile(r"matplotlib|\.(plot|hist|boxplot)\s*\(")


def precargar(codigo):
    """Importa de antemano las librerías pesadas para que no cuenten en el tiempo límite."""
    if "numpy" in codigo:
        import numpy  # noqa: F401
    if "pandas" in codigo:
        import pandas  # noqa: F401
    if _USA_GRAFICOS.search(codigo):
        try:
            import matplotlib
            matplotlib.use("agg")
            import matplotlib.pyplot  # noqa: F401
        except ImportError:
            pass


def _preparar_matplotlib(codigo, imagenes):
    if not _USA_GRAFICOS.search(codigo):
        return
    try:
        import matplotlib
        matplotlib.use("agg")
        import matplotlib.pyplot as plt
    except ImportError:
        return
    plt.close("all")

    def show(*args, **kwargs):
        imagenes.extend(_figuras_a_png())

    plt.show = show


def _linea_de_error(exc):
    tb = traceback.extract_tb(exc.__traceback__)
    lineas = [f.lineno for f in tb if f.filename == ARCHIVO]
    if isinstance(exc, SyntaxError) and exc.filename == ARCHIVO:
        return exc.lineno
    return lineas[-1] if lineas else None


def _formatear_error(exc):
    nombre = type(exc).__name__
    if isinstance(exc, SyntaxError):
        detalle = f"{nombre}: {exc.msg}"
        if exc.text:
            detalle += f"\n    {exc.text.rstrip()}"
    else:
        detalle = f"{nombre}: {exc}"
    linea = _linea_de_error(exc)
    pista = PISTAS.get(nombre)
    if pista is None:
        for base in type(exc).__mro__:
            if base.__name__ in PISTAS:
                pista = PISTAS[base.__name__]
                break
    return {"error": detalle, "linea": linea, "pista": pista or ""}


def _mostrar_valor(valor):
    """Devuelve (html, texto) para mostrar la última expresión, como hace Jupyter."""
    if valor is None:
        return None, None
    rep_html = getattr(valor, "_repr_html_", None)
    if callable(rep_html) and not isinstance(valor, type):
        try:
            html = rep_html()
            if html:
                return html, None
        except Exception:
            pass
    # Objetos de matplotlib (lo que devuelve plt.plot, df.plot...) no se muestran como texto.
    modulo = type(valor).__module__ or ""
    if modulo.startswith("matplotlib"):
        return None, None
    if isinstance(valor, list) and valor and (type(valor[0]).__module__ or "").startswith("matplotlib"):
        return None, None
    return None, repr(valor)


def ejecutar(codigo, check=None, entradas=None):
    salida = io.StringIO()
    imagenes = []
    resultado = {
        "stdout": "", "error": None, "linea": None, "pista": "",
        "imagenes": imagenes, "html": None, "check_ok": None, "check_msg": "",
    }
    espacio = {"__name__": "__main__", "__builtins__": builtins}
    viejo_out, viejo_err, viejo_input = sys.stdout, sys.stderr, builtins.input
    sys.stdout = sys.stderr = salida
    builtins.input = _Entradas(entradas)
    try:
        _preparar_matplotlib(codigo, imagenes)
        arbol = ast.parse(codigo, filename=ARCHIVO)
        ultima = None
        if arbol.body and isinstance(arbol.body[-1], ast.Expr):
            ultima = ast.Expression(arbol.body.pop().value)
        exec(compile(arbol, ARCHIVO, "exec"), espacio)
        if ultima is not None:
            valor = eval(compile(ultima, ARCHIVO, "eval"), espacio)
            html, texto = _mostrar_valor(valor)
            resultado["html"] = html
            if texto is not None:
                print(texto)
        imagenes.extend(_figuras_a_png())
    except BaseException as exc:  # incluye SystemExit / KeyboardInterrupt del usuario
        resultado.update(_formatear_error(exc))
    finally:
        sys.stdout, sys.stderr, builtins.input = viejo_out, viejo_err, viejo_input

    resultado["stdout"] = salida.getvalue()

    if check and resultado["error"] is None:
        espacio["_salida"] = resultado["stdout"]
        espacio["_codigo"] = codigo
        espacio["_imagenes"] = len(imagenes)
        try:
            exec(compile(check, "<revisión>", "exec"), espacio)
            resultado["check_ok"] = True
        except AssertionError as exc:
            resultado["check_ok"] = False
            resultado["check_msg"] = str(exc) or "Casi… el resultado todavía no es el esperado."
        except Exception as exc:
            resultado["check_ok"] = False
            resultado["check_msg"] = (
                "Tu código corre, pero falta algo que la revisión necesita "
                f"({type(exc).__name__}: {exc}). ¿Usaste los nombres de variables que pide el ejercicio?"
            )
    return resultado


def ejecutar_json(codigo, check=None, entradas=None):
    return json.dumps(ejecutar(codigo, check, entradas))
