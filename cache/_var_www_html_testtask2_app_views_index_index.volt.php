<div class="page-header">
    <h1>Welcome to my Test Task!</h1>
</div>

<?= $this->getContent() ?>

<p>If you want to continue, you should use an existing user account or create new one.</p>

<p>
    <span><?= $this->tag->linkTo(['users/signin', 'I have account. Sign in!', 'class' => 'btn btn-primary btn-large']) ?></span>
    <span><?= $this->tag->linkTo(['users/signup', 'No account. Sign up!', 'class' => 'btn btn-primary btn-large']) ?></span>
</p>
