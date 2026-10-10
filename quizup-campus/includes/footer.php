</main>
<footer class="footer">
    <span>QuizUp Campus &middot; Revise smarter, one streak at a time.</span>
</footer>
<script src="assets/js/app.js"></script>
<?php if (!empty($pageScripts)): foreach ($pageScripts as $s): ?>
<script src="assets/js/<?= e($s) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
