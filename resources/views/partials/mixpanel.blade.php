<script>
    {!! file_get_contents(public_path('vendor/genealabs-laravel-mixpanel/js/mixpanel.js')) !!}
    mixpanel.init("{{ config('services.mixpanel.token')}}");
</script>
