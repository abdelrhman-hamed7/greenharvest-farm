    </main>

    <footer class="site-footer mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand brand-mark text-white mb-3 d-inline-flex" href="<?php echo e($brandHref ?? (($basePath ?? '') . 'home.php')); ?>">
                        <span class="brand-icon brand-icon-light"><i class="bi bi-flower1"></i></span>
                        <span>
                            <strong>GreenHarvest</strong>
                            <small>Farm</small>
                        </span>
                    </a>
                    <p class="footer-text">
                        Fresh Organic Products from Our Farm to Your Doorstep.
                    </p>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <h6>Explore</h6>
                    <ul class="footer-links">
                        <li><a href="<?php echo $basePath ?? ''; ?>home.php">Home</a></li>
                        <li><a href="<?php echo $basePath ?? ''; ?>products.php">Products</a></li>
                        <li><a href="<?php echo $basePath ?? ''; ?>about.php">About</a></li>
                    </ul>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <h6>Customer Care</h6>
                    <ul class="footer-links">
                        <li><span><i class="bi bi-telephone me-2"></i>+250794090015</span></li>
                        <li><span><i class="bi bi-envelope me-2"></i>greenharvest@gmail.com</span></li>
                        <li><span><i class="bi bi-geo-alt me-2"></i>Kigali, Rwanda</span></li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6>Opening Hours</h6>
                    <p class="footer-text mb-1">Monday to Saturday</p>
                    <p class="footer-text mb-0">8:00 AM to 6:00 PM</p>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?php echo date('Y'); ?> GreenHarvest Farm. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $basePath ?? ''; ?>js/main.js?v=20260612"></script>
</body>
</html>
