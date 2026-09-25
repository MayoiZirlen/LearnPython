"""
Prueba automática del contenido del curso.

Para cada lección verifica que:
  * los ejemplos corren sin error (salvo los marcados con 'expect_error'),
  * la solución de cada ejercicio pasa su revisión,
  * el código inicial (starter) NO pasa la revisión,
  * los quizzes tienen una respuesta válida.

Uso (desde la carpeta del proyecto):
    python tools/test_lessons.py            # requiere numpy, pandas y matplotlib
    python tools/test_lessons.py pandas-csv # solo una lección
"""
import json
import os
import subprocess
import sys

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, os.path.join(RAIZ, "assets", "py"))
os.environ.setdefault("MPLBACKEND", "agg")
import harness  # noqa: E402

os.chdir(os.path.join(RAIZ, "data"))

php = os.environ.get("PHP", "php")
lecciones = json.loads(subprocess.check_output([php, os.path.join(RAIZ, "tools", "export_content.php")]))
filtro = set(sys.argv[1:])

fallos = 0
avisos = 0
for lec in lecciones:
    if filtro and lec["slug"] not in filtro:
        continue
    for i, paso in enumerate(lec["steps"]):
        donde = f"{lec['slug']} paso {i} ({paso['type']})"
        t = paso["type"]
        if t == "example":
            r = harness.ejecutar(paso["code"])
            if bool(r["error"]) != bool(paso.get("expect_error")):
                fallos += 1
                print(f"❌ {donde}: error inesperado -> {r['error']}")
        elif t == "exercise":
            r = harness.ejecutar(paso["solution"], paso["check"])
            if not r["check_ok"]:
                fallos += 1
                print(f"❌ {donde}: la solución no pasa -> {r['error'] or r['check_msg']}")
            r = harness.ejecutar(paso.get("starter", ""), paso["check"])
            if r["check_ok"]:
                avisos += 1
                print(f"⚠️  {donde}: el código inicial ya pasa la revisión")
        elif t == "quiz":
            if not (0 <= paso["answer"] < len(paso["options"])):
                fallos += 1
                print(f"❌ {donde}: respuesta fuera de rango")
    print(f"✔ {lec['slug']}")

print(f"\n{fallos} fallos, {avisos} avisos")
sys.exit(1 if fallos else 0)
