
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div class="mobile-sidebar-overlay" onclick="toggleMobileMenu()"></div>

<script src="http://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="./assets/scripts/main.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/myjs.js"></script>
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/sweetalert.js"></script>

<!-- Mobile Menu JavaScript -->
<script>
function toggleMobileMenu() {
    $('.app-sidebar').toggleClass('mobile-open');
    $('.mobile-sidebar-overlay').toggleClass('active');
}

// Close mobile menu when clicking on menu items
$('.app-sidebar a').on('click', function() {
    if (window.innerWidth <= 767) {
        toggleMobileMenu();
    }
});

// Handle window resize
$(window).resize(function() {
    if (window.innerWidth > 767) {
        $('.app-sidebar').removeClass('mobile-open');
        $('.mobile-sidebar-overlay').removeClass('active');
    }
});

// Touch swipe to close menu
let startX = 0;
let currentX = 0;
let isSwipe = false;

$('.app-sidebar').on('touchstart', function(e) {
    startX = e.touches[0].clientX;
    isSwipe = true;
});

$('.app-sidebar').on('touchmove', function(e) {
    if (!isSwipe) return;
    currentX = e.touches[0].clientX;
    let diffX = startX - currentX;
    
    if (diffX > 50) { // Swipe left to close
        toggleMobileMenu();
        isSwipe = false;
    }
});

$('.app-sidebar').on('touchend', function() {
    isSwipe = false;
});
</script>

</body>
</html>




