<script>
{!! file_get_contents(public_path('genealabs-laravel-mixpanel/js/mixpanel.js')) !!}
mixpanel.init("{{ config('services.mixpanel.token') }}",  {api_host: "https://api.mixpanel.com"});
</script>
