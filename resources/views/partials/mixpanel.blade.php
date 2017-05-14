<script src="{{ asset('genealabs-laravel-mixpanel/js/mixpanel.js') }}"></script>
<script>
    mixpanel.init("{{ config('services.mixpanel.token') }}");
</script>
