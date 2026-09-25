<?php
return [
    'title' => '¡Hola, Python!',
    'icon' => '👋',
    'minutes' => 6,
    'summary' => 'Qué es programar y tu primer programa con print().',
    'steps' => [
        [
            'type' => 'text',
            'title' => '¿Qué es programar?',
            'html' => <<<'HTML'
<p>Programar es <b>darle instrucciones a una computadora</b>, paso a paso, en un idioma que entienda.</p>
<div class="analogy">🍳 <b>Piensa en una receta de cocina:</b> "rompe 2 huevos, bátelos, ponlos en el sartén". Un programa es igual: una lista de instrucciones que se ejecutan <b>en orden, de arriba hacia abajo</b>.</div>
<p><b>Python</b> es uno de esos idiomas, y es famoso por ser fácil de leer. Lo usan Netflix, la NASA, Spotify… y es <b>la herramienta número 1 de los analistas de datos</b>. 📊</p>
<p>Al final de este curso vas a poder cargar una tabla con cientos de ventas, limpiarla, sacar conclusiones y hacer gráficos. ¡Pero empecemos por el principio!</p>
HTML,
        ],
        [
            'type' => 'example',
            'title' => 'Tu primer programa',
            'html' => '<p>La instrucción <code>print()</code> muestra en pantalla lo que pongas dentro de los paréntesis. Pulsa <b>▶ Ejecutar</b> y mira qué pasa:</p>',
            'code' => <<<'PY'
print("¡Hola, mundo!")
print("Estoy aprendiendo Python 🐍")
print(2 + 3)
PY,
        ],
        [
            'type' => 'text',
            'title' => 'Texto vs. números',
            'html' => <<<'HTML'
<p>¿Notaste la diferencia?</p>
<ul>
  <li><code>print("¡Hola, mundo!")</code> → el texto va <b>entre comillas</b>. A los textos en programación les llamamos <b>strings</b> (cadenas).</li>
  <li><code>print(2 + 3)</code> → sin comillas, Python <b>calcula</b> y muestra <code>5</code>.</li>
</ul>
<p>También puedes escribir <b>comentarios</b> con <code>#</code>. Python los ignora; sirven para dejar notas a los humanos:</p>
<pre># Esto es un comentario, Python no lo ejecuta
print("Esto sí se ejecuta")  # y esto también es comentario</pre>
<div class="tip">💡 Puedes usar comillas dobles <code>"hola"</code> o simples <code>'hola'</code>. Las dos funcionan igual.</div>
HTML,
        ],
        [
            'type' => 'quiz',
            'question' => '¿Qué muestra este código?',
            'code' => 'print("5 + 3")',
            'options' => ['8', '5 + 3', 'Un error', 'Nada'],
            'answer' => 1,
            'explain' => 'Como <code>5 + 3</code> está entre comillas, Python lo trata como <b>texto</b> y lo muestra tal cual, sin sumar.',
            'wrong' => [0 => 'Ojo con las comillas: todo lo que está entre comillas es texto, no una operación.'],
        ],
        [
            'type' => 'example',
            'title' => 'Los errores son tus amigos',
            'expect_error' => true,
            'html' => '<p>Equivocarse es parte de programar (¡incluso los expertos se equivocan todo el día!). Este código tiene un error a propósito. Ejecútalo, lee el mensaje y luego <b>arréglalo</b> agregando la comilla que falta.</p>',
            'code' => <<<'PY'
print("Me faltan las comillas del final)
PY,
        ],
        [
            'type' => 'exercise',
            'title' => 'Preséntate',
            'html' => '<p>Escribe un programa que muestre <b>dos líneas</b>:</p><ol><li>Tu nombre (por ejemplo <code>Me llamo Ana</code>)</li><li>Una frase que incluya la palabra <code>Python</code></li></ol>',
            'starter' => "# Escribe tus dos print() aquí abajo\n",
            'hint' => ['Necesitas dos instrucciones <code>print()</code>, una en cada línea.', 'No olvides las comillas alrededor del texto: <code>print("Me llamo Ana")</code>'],
            'solution' => "print(\"Me llamo Ana\")\nprint(\"Voy a dominar Python\")\n",
            'check' => <<<'PY'
lineas = [l for l in _salida.strip().splitlines() if l.strip()]
assert len(lineas) >= 2, "Tu programa debe mostrar al menos 2 líneas. ¿Usaste dos print()?"
assert "python" in _salida.lower(), "Alguna línea debe incluir la palabra Python."
PY,
            'success' => '¡Tu primer programa funciona! Oficialmente eres programador/a. 🎉',
        ],
        [
            'type' => 'quiz',
            'question' => '¿Cuál de estas líneas <b>NO</b> muestra nada en pantalla?',
            'options' => ['print("hola")', '# print("hola")', "print('hola')", 'print(10)'],
            'answer' => 1,
            'explain' => 'El <code>#</code> convierte la línea en un comentario, así que Python la ignora por completo.',
        ],
        [
            'type' => 'exercise',
            'title' => 'Arte con texto',
            'html' => '<p>Con <code>print()</code> también puedes dibujar. Crea un pequeño <b>letrero de al menos 3 líneas</b>. ¡Usa tu creatividad! Por ejemplo:</p><pre>*****************
*  DATA ANALYST *
*****************</pre>',
            'starter' => "print(\"*****************\")\n",
            'hint' => 'Cada línea del dibujo es un <code>print()</code> distinto.',
            'solution' => "print(\"*****************\")\nprint(\"*  DATA ANALYST *\")\nprint(\"*****************\")\n",
            'check' => <<<'PY'
lineas = [l for l in _salida.splitlines() if l.strip()]
assert len(lineas) >= 3, f"Tu letrero tiene {len(lineas)} línea(s). ¡Necesita al menos 3!"
PY,
        ],
    ],
];
