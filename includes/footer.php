</div> <!-- Fermeture du container principal si ouvert dans le header -->
    <footer class="mt-5 py-4 neumorphic">
        <div class="container text-center">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Tous droits réservés.</p>
        </div>
    </footer>
    <script src="/projet/assets/js/cart.js"></script>
    <script src="/projet/assets/js/search.js"></script>
    <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <script src="/projet/assets/js/admin.js"></script>
    <?php endif; ?>
</body>
</html>