<script language="javascript">
    function resizeIframe(obj, host_id) {
        if ('cols' in localStorage) {
            localStorage.removeItem('cols');
            return;
        }

        localStorage['cols'] = 2;
        if (window.innerWidth < 800) {
            localStorage['cols'] = 1;
        }

        obj.src='/cacti/graph_view.php?action=preview&columns=' + localStorage['cols'] + '&host_id=' + host_id;

        setTimeout(function() { obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px'; }, 3000);
    };
</script>
