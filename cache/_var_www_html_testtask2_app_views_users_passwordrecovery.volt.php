<div class="page-header">
    <h2>Password recovery</h2>
</div>

<?= $this->getContent() ?>

<?php if ((empty($id))) { ?>
    
    <div id="box" class="col-md-4">
        <?= $this->tag->form(['users/passwordRecovery', 'class' => 'form-horizontal', 'onbeforesubmit' => 'return false']) ?>
        <fieldset>
            <p>Enter your e-mail, and we will send you further instructions.</p>
            <div class="form-group">
                <div class="col-md-9">
                    <?= $this->tag->textField(['email', 'class' => 'form-control', 'placeholder' => 'your email']) ?>
                </div>
                <?= $this->tag->submitButton(['Send', 'class' => 'btn btn-info ']) ?>
            </div>
        </fieldset>
        </form>
    </div>

<?php } else { ?>
    
    <div id="box" class="col-md-4">
        <?= $this->tag->form(['users/passwordRecovery', 'class' => 'form-horizontal', 'onbeforesubmit' => 'return false']) ?>
        <fieldset>
            <p>Point your new password:</p>
            <input type='hidden' name='id' value='<?= $id ?>'/>

            <div class="form-group">
                <label for="password" class="col-md-3 control-label">Password</label>
                <div class="col-md-9">
                    <?= $this->tag->passwordField(['password', 'class' => 'form-control', 'placeholder' => 'password']) ?>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="col-md-3 control-label">Repeat</label>
                <div class="col-md-9">
                    <?= $this->tag->passwordField(['repeatPassword', 'class' => 'form-control', 'placeholder' => 'repeat password']) ?>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-offset-3 col-md-9">
                    <?= $this->tag->submitButton(['Submit', 'class' => 'btn btn-info ']) ?>
                </div>
            </div>
        </fieldset>
        </form>
    </div>
<?php } ?>