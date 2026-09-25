</main>
<footer class="footer">
  Hecho con 🐍 y ☕ · Python corre en tu navegador gracias a <a href="https://pyodide.org" target="_blank" rel="noopener">Pyodide</a>
</footer>
<div id="toasts"></div>
<script src="assets/js/app.js"></script>
<?php if (!empty($usesPython)): ?>
<script src="assets/js/python.js"></script>
<?php endif; ?>
<?php foreach ($extraScripts ?? [] as $s): ?>
<script src="<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
