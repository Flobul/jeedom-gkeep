/* Rafraîchissement de la page Santé, sans modifier les réglages du plugin. */
(function () {
    var button = document.getElementById('bt_refreshHealth_gkeep');
    if (!button) return;
    button.addEventListener('click', function () {
        var dialog = button.closest('.jeeDialog');
        jeeDialog.dialog({
            id: dialog && dialog.id ? dialog.id : 'md_gkeepHealth',
            title: '{{Santé Google Keep}}',
            contentUrl: 'index.php?v=d&plugin=gkeep&modal=health'
        });
    });
})();
