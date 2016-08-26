<script src="{!! assets('vendor/genealabs/laravel-mixpanel/js/mixpanel.js') !!}"></script>
<script>
    mixpanel.init("{{ config('genealabs-laravel-mixpanel.client-key')}}");
</script>
