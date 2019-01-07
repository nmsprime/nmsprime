<script language="javascript">
    function resizeIframe(obj) {
        setTimeout(function() {
            var height = obj.contentWindow.document.body.scrollHeight;
            /* workaround ipad (safari?) bug */
            if (height < 1000) {
                height *= 15;
            }
            obj.style.height = height + 'px';
        }, 5000);
    };
</script>
