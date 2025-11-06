<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
    window.onload = function () {
        google.accounts.id.initialize({
            client_id: '{{ config('services.google.client_id') }}',
            callback: handleCredentialResponse,
            auto_select: {{ $autoSelect ?? 'false' }},
            cancel_on_tap_outside: false
        });

        google.accounts.id.prompt();
    };

    function handleCredentialResponse(response) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('google.callback') }}';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'credential';
        tokenInput.value = response.credential;
        form.appendChild(tokenInput);

        document.body.appendChild(form);
        form.submit();
    }
</script>
