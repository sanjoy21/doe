<div id="dek"></div>

<script type="text/javascript">
// Modern popup/tooltip functionality
var Xoffset = -60;    // Horizontal offset
var Yoffset = 20;     // Vertical offset
var popupActive = false;
var dekElement = document.getElementById('dek');

// Initialize the popup element
if (dekElement) {
    dekElement.style.position = 'absolute';
    dekElement.style.visibility = 'hidden';
    dekElement.style.display = 'none';
    dekElement.style.zIndex = '1000';
    dekElement.style.pointerEvents = 'none'; // Allow clicks to pass through
}

function popup(msg, bak) {
    if (!dekElement) return;
    
    var content = '<div style="background-color:' + bak + '; border:1px solid black; padding:5px; max-width:250px; font-size:12px; color:black;">' + 
                  msg + '</div>';
    
    dekElement.innerHTML = content;
    dekElement.style.display = 'block';
    dekElement.style.visibility = 'visible';
    popupActive = true;
}

function get_mouse(e) {
    if (!dekElement || !popupActive) return;
    
    // Get mouse position with cross-browser support
    var x = 0, y = 0;
    
    if (e.pageX || e.pageY) {
        x = e.pageX;
        y = e.pageY;
    } else if (e.clientX || e.clientY) {
        x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }
    
    // Apply offsets
    dekElement.style.left = (x + Xoffset) + 'px';
    dekElement.style.top = (y + Yoffset) + 'px';
}

function kill() {
    if (!dekElement) return;
    
    dekElement.style.display = 'none';
    dekElement.style.visibility = 'hidden';
    popupActive = false;
}

// Set up event listeners
document.addEventListener('mousemove', get_mouse);

// Clean up when page is unloaded
window.addEventListener('beforeunload', function() {
    document.removeEventListener('mousemove', get_mouse);
});

// Alternative: If you need to support older browsers without addEventListener
if (document.attachEvent) {
    document.attachEvent('onmousemove', get_mouse);
}
</script>