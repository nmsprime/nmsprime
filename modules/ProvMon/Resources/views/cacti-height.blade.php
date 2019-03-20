<script language="javascript">
    function resizeIframe(obj) {
        setTimeout(function() {
            var height = obj.contentWindow.document.body.scrollHeight;
            obj.style.height = height + 'px';
            reloadIframeQuick(obj);
        }, 5000);
    };

    var rif = 0;
    function reloadIframeQuick(obj) {
        if (rif != 1) {
            rif = 1;
            obj.src = obj.src;
        }
    }
</script>
