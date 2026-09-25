<?php
return [
    'title' => 'Si pasa esto, haz aquello',
    'icon' => '🔀',
    'minutes' => 9,
    'summary' => 'if, elif y else: tu programa toma decisiones.',
    'steps' => [
        [
            'type' => 'text',
            'title' => 'La instrucción if',
            'html' => <<<'HTML'
<p>Con <code>if</code> (si) el programa ejecuta un bloque de código <b>solo si</b> una condición es verdadera. Con <code>else</code> (si no) indicas qué hacer en caso contrario:</p>
<pre>temperatura = 32

if temperatura > 30:
    print("¡Hace calor! 🥵")
    print("Toma agua")
else:
    print("Clima agradable 😎")</pre>
<p>Fíjate en dos detalles <b>muy importantes</b>:</p>
<ol>
  <li>La línea del <code>if</code> termina con <b>dos puntos</b> <code>:</code></li>
  <li>Lo que va "dentro" del if lleva <b>sangría</b>: 4 espacios al inicio (la tecla Tab lo hace por ti).</li>
</ol>
<div class="analogy">🚦 La sangría es como las viñetas de una lista: le dice a Python qué instrucciones "pertenecen" al if.</div>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Pruébalo',
            'html' => '<p>Cambia el valor de <code>temperatura</code> y vuelve a ejecutar.</p>',
            'code' => <<<'PY'
temperatura = 32

if temperatura > 30:
    print("¡Hace calor! 🥵")
    print("Toma agua")
else:
    print("Clima agradable 😎")

print("Esta línea siempre se ejecuta (no tiene sangría)")
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Varias opciones con elif',
            'html' => <<<'HTML'
<p>Cuando hay más de dos caminos, usa <code>elif</code> (abreviatura de "else if"). Python revisa las condiciones <b>en orden</b> y ejecuta <b>solo la primera</b> que sea verdadera:</p>
<pre>ventas = 8500

if ventas >= 10000:
    nivel = "🥇 Oro"
elif ventas >= 5000:
    nivel = "🥈 Plata"
elif ventas >= 1000:
    nivel = "🥉 Bronce"
else:
    nivel = "Sin medalla"

print(nivel)   # 🥈 Plata</pre>
<div class="tip">💡 Esto es exactamente lo que hacen los analistas para <b>segmentar</b> clientes: "premium", "regular", "nuevo"…</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué muestra este código?',
            'code' => "x = 15\nif x > 20:\n    print(\"A\")\nelif x > 10:\n    print(\"B\")\nelif x > 5:\n    print(\"C\")\nelse:\n    print(\"D\")",
            'options' => ['A', 'B', 'B y C', 'D'],
            'answer' => 1,
            'explain' => '15 no es mayor que 20, pero sí es mayor que 10 → muestra <b>B</b>. Aunque también es mayor que 5, Python ya no revisa las demás: se queda con la primera verdadera.',
            'wrong' => [2 => 'Solo se ejecuta la <b>primera</b> condición verdadera de la cadena if/elif.'],
        ],
        [
            'type' => 'exercise',
            'title' => 'Calificación con letras',
            'html' => <<<'HTML'
<p>Según la variable <code>nota</code>, guarda en <code>resultado</code>:</p>
<ul>
  <li><code>"Excelente"</code> si la nota es 90 o más</li>
  <li><code>"Aprobado"</code> si es 70 o más</li>
  <li><code>"Reprobado"</code> en cualquier otro caso</li>
</ul>
<p>Usa <code>if</code>, <code>elif</code> y <code>else</code>. Luego prueba cambiando la nota para ver que funcione en todos los casos.</p>
HTML,
            'starter' => "nota = 85\n\n# Escribe tu if / elif / else aquí\n\n\nprint(resultado)\n",
            'hint' => ['Empieza con la condición más alta: <code>if nota >= 90:</code>', 'No olvides los dos puntos <code>:</code> y la sangría de 4 espacios.'],
            'solution' => "nota = 85\n\nif nota >= 90:\n    resultado = \"Excelente\"\nelif nota >= 70:\n    resultado = \"Aprobado\"\nelse:\n    resultado = \"Reprobado\"\n\nprint(resultado)\n",
            'check' => <<<'PY'
assert "elif" in _codigo and "else" in _codigo, "Usa if, elif y else."
assert resultado == ("Excelente" if nota >= 90 else "Aprobado" if nota >= 70 else "Reprobado"), f"Con nota = {nota} el resultado no es el correcto: obtuviste {resultado!r}."
# Probamos tu lógica con otras notas, cambiando la primera línea
import re
for prueba, esperado in [(95, "Excelente"), (70, "Aprobado"), (40, "Reprobado"), (90, "Excelente")]:
    ns = {}
    exec(re.sub(r"^nota\s*=.*$", f"nota = {prueba}", _codigo, count=1, flags=re.M).replace("print(", "(lambda *a, **k: None)("), ns)
    assert ns.get("resultado") == esperado, f"Con nota = {prueba} deberías obtener {esperado!r}, pero obtuviste {ns.get('resultado')!r}."
PY,
            'success' => '¡Probamos tu código con las notas 95, 90, 70 y 40 y funcionó en todos los casos! 🎯',
        ],
        [
            'type' => 'exercise',
            'title' => '🛒 Descuento por compra grande',
            'html' => '<p>Si el <code>total</code> de la compra es <b>mayor a 1000</b>, se aplica un <b>10% de descuento</b>. Si no, se paga completo. Guarda el resultado en <code>total_final</code> y muéstralo.</p>',
            'starter' => "total = 1200\n\n",
            'hint' => ['El 10% de descuento es pagar el 90%: <code>total * 0.9</code>', 'En el <code>else</code>, <code>total_final = total</code>'],
            'solution' => "total = 1200\n\nif total > 1000:\n    total_final = total * 0.9\nelse:\n    total_final = total\n\nprint(total_final)\n",
            'check' => <<<'PY'
assert "if" in _codigo, "Usa un if para decidir si aplica el descuento."
assert abs(total_final - 1080) < 1e-9, f"Con un total de 1200 el total_final debería ser 1080, obtuviste {total_final}."
import re
ns = {}
exec(re.sub(r"^total\s*=.*$", "total = 800", _codigo, count=1, flags=re.M).replace("print(", "(lambda *a, **k: None)("), ns)
assert abs(ns["total_final"] - 800) < 1e-9, "Si el total es 800 no debería haber descuento. ¿Tienes el else?"
PY,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué error tiene este código?',
            'code' => "if ventas > 100\n    print(\"¡Bien!\")",
            'options' => ['Falta la sangría', 'Faltan los dos puntos después de la condición', 'print está mal escrito', 'No tiene error'],
            'answer' => 1,
            'explain' => 'La línea del <code>if</code> siempre debe terminar con <code>:</code>. Es el error número 1 de quienes empiezan… ¡ahora ya lo sabes!',
        ],
    ],
];
