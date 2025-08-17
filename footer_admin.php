<?php
// /includes/footer_admin.php
?>
</div> <script src="/assets/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript untuk toggle sidebar
        const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('adminSidebar');
                const overlay = document.getElementById('overlay');

                    function toggleMenu() {
                            if(sidebar && overlay) {
                                        sidebar.classList.toggle('show');
                                                    overlay.classList.toggle('show');
                                                            }
                                                                }

                                                                    if (menuToggle) {
                                                                            menuToggle.addEventListener('click', toggleMenu);
                                                                                }
                                                                                    if (overlay) {
                                                                                            overlay.addEventListener('click', toggleMenu);
                                                                                                }
                                                                                                </script>
                                                                                                </body>
                                                                                                </html>