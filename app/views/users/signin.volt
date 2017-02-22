<div class="page-header">
    <h2>Login</h2>
</div>

{{ content() }}

<div id="signinbox" class="col-md-4">
    {{ form('users/signin', 'class': 'form-horizontal', 'onbeforesubmit': 'return false') }}
        <fieldset>
            <div class="form-group">
                <label for="email" class="col-md-3 control-label">Email</label>
                <div class="col-md-9">
                    {{ text_field('email', 'class': "form-control", 'placeholder':"your email") }}
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="col-md-3 control-label">Password</label>
                <div class="col-md-9">
                    {{ password_field('password', 'class': "form-control", 'placeholder':"password") }}
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                    {{ submit_button('Login', 'class': 'btn btn-info ') }}
                    <a href="/users/signup" class="col-md-offset-1">Create account</a>
                </div>
                <br>
                <br>
                <div class="col-md-offset-3 col-md-9 ">
                    <a href="/users/passwordRecovery" class="text-warning">Forgot password?</a>
                </div>
            </div>
        </fieldset>
    </form>
</div>

