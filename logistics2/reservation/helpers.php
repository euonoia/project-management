<?php
function get_progress_percent($status) {
    switch ($status) {
        case 'Pending': return 20;
        case 'Approved': return 40;
        case 'Dispatched': return 70;
        case 'Completed': return 100;
        case 'Cancelled': return 100;
        default: return 0;
    }
}
?>
