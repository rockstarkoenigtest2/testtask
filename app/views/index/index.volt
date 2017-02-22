<div class="page-header">
    <h1>Welcome to my Test Task!</h1>
</div>

{{ content() }}

<p>If you want to continue, you should use an existing user account or create new one.</p>

<p>
    <span>{{ link_to('users/signin', 'I have account. Sign in!', 'class': 'btn btn-primary btn-large') }}</span>
    <span>{{ link_to('users/signup', 'No account. Sign up!', 'class': 'btn btn-primary btn-large') }}</span>
</p>
