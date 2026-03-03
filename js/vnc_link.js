$(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var computerId = urlParams.get('id');
    
    if (!computerId || computerId <= 0) {
        return;
    }
    
    var btn = $('<button>', {
        type: 'button',
        class: 'btn btn-sm btn-secondary ms-2',
        text: 'VNC Link',
        style: 'font-weight: 400;'
    });

    btn.on('click', function() {
        window.open(
            CFG_GLPI.root_doc + '/plugins/vnc_link/ajax/vnc_link.php?computers_id=' + computerId,
            '_blank'
        );
    });

    $('.navigationheader .center.nav_title').after(btn);
});
