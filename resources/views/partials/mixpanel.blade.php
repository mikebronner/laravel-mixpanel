<script src="{{ asset('genealabs-laravel-mixpanel/js/mixpanel.js') }}"></script>
<script>
    if ("{{ config('mixpanel.token') }}" == "") {
        console.error('Mixpanel for Laravel: You must declare the env variable MIXPANEL_TOKEN!');
    }

    mixpanel.init("{{ config('mixpanel.token') }}");
</script>
