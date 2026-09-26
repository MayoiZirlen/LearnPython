</main>
<footer class="footer">
  Hecho con 🐍 y ☕ · Python corre en tu navegador gracias a <a href="https://pyodide.org" target="_blank" rel="noopener">Pyodide</a>
</footer>
<div id="toasts"></div>
<script src="<?= e(asset('assets/js/sonidos.js')) ?>"></script>
<script src="<?= e(asset('assets/js/app.js')) ?>"></script>
<?php if (!empty($usesPython)): ?>
<script src="<?= e(asset('assets/js/python.js')) ?>"></script>
<?php endif; ?>
<?php foreach ($extraScripts ?? [] as $s): ?>
<script src="<?= e(asset($s)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
