<footer class="app-footer"> 
            <div class="float-end d-none d-sm-inline">Version 1.0</div>
            Copyright © <span id="currentyear"></span>, DIUEEE. All rights reserved. Developed by <a href="https://www.linkedin.com/in/mobassermax/" target="_blank">Mobasser</a>
        </footer>
    </div>
    
    <script src="js/jquery.min.js"></script>
    <script src="js/overlayscrollbars.browser.es6.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/dashboard-scripts.js"></script>
    
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>