<div class="page-header">
    <h2>Create account</h2>
</div>

{{ content() }}

<div id="signupbox" class="col-md-4">
    {{ form('users/signup', 'class': 'form-horizontal', 'onbeforesubmit': 'return false') }}
        <fieldset>
            <div class="form-group">
                <label for="email" class="col-md-3 control-label">Name</label>
                <div class="col-md-9">
                    {{ text_field('name', 'class': "form-control", 'placeholder':"your name") }}
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="col-md-3 control-label">Email</label>
                <div class="col-md-9">
                    {{ text_field('email', 'class': "form-control", 'placeholder':"your email") }}
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label">Groups</label>

                <div class="col-md-9">
                    {% for item in groups %}
                        <div class="checkbox">
                          <label><input type="checkbox" name="groups[]" value="{{ item.id }}" {{ item.id == 1 ? "checked" : "" }}>{{ item.name }}</label>
                        </div>
                    {% endfor %}
                 </div>
            </div>


            <div class="form-group">
                <label for="password" class="col-md-3 control-label">Password</label>
                <div class="col-md-9">
                    {{ password_field('password', 'class': "form-control", 'placeholder':"password") }}
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="col-md-3 control-label">Repeat password</label>
                <div class="col-md-9">
                    {{ password_field('repeatPassword', 'class': "form-control", 'placeholder':"repeat password") }}
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                    {{ submit_button('Create account', 'class': 'btn btn-info ') }}
                    <a href="/users/signin" class="col-md-offset-1">I have account</a>
                </div>
            </div>
        </fieldset>
    </form>
</div>
