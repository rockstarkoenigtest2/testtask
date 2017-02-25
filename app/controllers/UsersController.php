<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;
use Phalcon\Tag as Tag;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Mvc\View\Engine\Volt;


class UsersController extends ControllerBase
{
    const PAGE_CAPACITY = 5;

    public function indexAction()
    {
        if (!$this->session->get('id')) {
            return $this->forward('');
        }

        $page = $this->request->get("page");
        $query = $this->request->get("search");

        $builder = Users::query()->join('Groups');
        if (!empty($query)) {
            $builder
                ->where('Groups.name LIKE :query:', ['query' => "%" . $query . "%"])
                ->orWhere('email LIKE :query:', ['query' => "%" . $query . "%"]);
        }
        $builder->columns("Users.id, Users.name, Users.email, GROUP_CONCAT(Groups.name) AS group_names");
        $builder->groupBy("Users.id");
        $users = $builder->execute();

        $pageMax = max(1, ceil(count($users) / UsersController::PAGE_CAPACITY));
        if ($page == 'last' || $page > $pageMax) {
            return $this->forward('users?page=' . $pageMax);
        }

        $paginator = new PaginatorModel(
            [
                "data"  => $users,
                "limit" => UsersController::PAGE_CAPACITY,
                "page"  => $page
            ]
        );

        $groups = Groups::find();
        $this->view->pagingUrl = empty($query) ? "users&page=" : "users?search=" . $query . "&page=";
        $this->view->groups = $groups;
        $this->view->query = $query;
        $this->view->totalCount = count($users);
        $this->view->page = $paginator->getPaginate();
    }

    public function deleteAction() {
        if (!$this->session->get('can_edit')) {
            return $this->generateErrorResponse('You have no permission to do this.');
        }

        $this->view->disable();
        $uid = $this->request->getPost("id");
        $user = Users::findFirst($uid);

        if (empty($user)) {
            return $this->generateErrorResponse('Cannot find user with pointed ID.');
        }

        if ($user->delete() === false) {
            return $this->generateErrorResponse('Unknown error');
        } else {
            $user->UsersGroups->delete();
        }

        return json_encode(['result' => 'ok']);
    }

    public function createAction() {
        if (!$this->session->get('can_edit')) {
            return $this->generateErrorResponse('You have no permission to do this.');
        }

        $this->view->disable();

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $groups = $this->request->getPost('groups');
        $repeatPassword = $this->request->getPost('repeatPassword');

        if (count($groups) == 0) {
            return $this->generateErrorResponse('Please, check at least one group.');
        }

        if ($password != $repeatPassword) {
            return $this->generateErrorResponse('Passwords are diferent');
        }

        if (strlen($password) < 6) {
            return $this->generateErrorResponse('Password is too short!');
        }

        $user = new Users();
        $user->password = sha1($password);
        $user->name = $name;
        $user->email = $email;
        $user->groups = Groups::find([
            'id IN ({id:array})',
            'bind' => ['id' => $groups]
        ]);

        if ($user->save() == false) {
            $errors = [];
            foreach ($user->getMessages() as $message) {
                $errors[] = (string) $message;
            }

            return $this->generateErrorResponse(implode('; ', $errors));
        }

        return json_encode(['result' => 'ok']);
    }

    public function updateAction() {
        if (!$this->session->get('can_edit')) {
            return $this->generateErrorResponse('You have no permission to do this.');
        }

        $this->view->disable();

        $uid = $this->request->getPost("id");
        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");
        $groups = $this->request->getPost('groups');

        $user = Users::findFirst($uid);

        if (empty($user)) {
            return $this->generateErrorResponse('Cannot find user with pointed ID.');
        }

        if (empty($groups)) {
            return $this->generateErrorResponse('Please, check at least one group.');
        }

        $user->name = $name;
        $user->email = $email;
        $user->UsersGroups->delete();
        $user->groups = Groups::find([
            'id IN ({id:array})',
            'bind' => ['id' => $groups]
        ]);

        if ($user->save() == false) {
            $errors = [];
            foreach ($user->getMessages() as $message) {
                $errors[] = (string) $message;
            }

            return $this->generateErrorResponse(implode('; ', $errors));
        }

        return json_encode(['result' => 'ok']);
    }

    public function passwordRecoveryAction() {
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $repeatPassword = $this->request->getPost('repeatPassword');
            $id = $this->request->getPost('id');

            if (!empty($email)) {

                // Generate and send recovery e-mail

                $user = Users::findFirst(['email = (:email:)', 'bind' => ['email' => $email]]);

                if (empty($user)) {
                    $this->flash->error('Cannot find user with such e-mail: ' . $email);
                    return;
                }

                $token = uniqid();
                $user->token = $token;
                if ($user->save() === false) {
                    $this->flash->error("Unknown error with password recovering. Please try again later.");
                    return;
                }

                $this->sendRecoveryEMail($email, $user->id, $token);

                $this->flashSession->success('We have sent instructions to ' . $email . '. Check it.');
                return $this->forward("");
            } else if (!empty($password)) {

                // Changing user's password

                if ($password != $repeatPassword) {
                    $this->flash->error('Passwords are diferent');
                    $this->view->id = $id;
                    return;
                }

                if (strlen($password) < 6) {
                    $this->flash->error('Password is too short!');
                    $this->view->id = $id;
                    return;
                }

                $user = Users::findFirst($id);
                if (empty($user)) {
                    $this->flash->error('Error with password recovering (user not found). Please try again later.');
                    return;
                }

                $user->password = sha1($password);
                $user->token = null;
                if ($user->save() === false) {
                    $this->flash->error('Error with password recovering (saving failed). Please try again later.');
                    return;
                }

                $this->flashSession->success('Your new password successfully changed! Use it to sign in.');
                return $this->forward("users/signin");
            }
        }

        // if no post-data, show form
        $id = $this->request->get("id");
        $token = $this->request->get("token");
        if (!empty($id)) {
            $user = Users::findFirst($id);

            if (empty($user)) {
                $this->flash->error('Unknown error with password recovering. Please try again later.');
                return;
            }

            if ($token != $user->token) {
                $this->flash->error('Error with password recovering: token mismatch. Please try again.');
                return;
            }
        }

        $this->view->id = $id;
    }

    private function sendRecoveryEMail($email, $id, $token) {
        include BASE_PATH . '/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

        $url = 'http://' . $_SERVER['SERVER_NAME'] . $this->url->get() . 'users/passwordRecovery?id=' . $id . '&token=' . $token;

        $message = Swift_Message::newInstance('Password recovery')
            ->setFrom(['rockstar.koenig.test2@gmail.com' => 'Test Task'])
            ->setTo([$email])
            ->setBody('To recover your password follow: ' . $url);

        $result = $this->mailer->send($message);
        echo $result;
    }

    public function signinAction()
    {
        $this->flashSession->output();
        if (!$this->request->isPost()) {
            return;
        }

        $email = $this->request->getPost('email', 'email');

        $password = $this->request->getPost('password');
        $password = sha1($password);

        $user = Users::findFirst(["conditions" => "email=:email: AND password=:password:", "bind" => ['email' => $email, 'password' => $password]]);
        if (!empty($user)) {
            $this->registerSession($user);
            return $this->forward('users');
        }

        $this->flash->error('Wrong email/password');
    }

    public function signupAction()
    {
        $request = $this->request;
        $this->view->groups = Groups::find();
        if (!$request->isPost()) {
            return;
        }

        $name = $request->getPost('name', array('string', 'striptags'));
        $email = $request->getPost('email', 'email');
        $password = $request->getPost('password');
        $groups = $request->getPost('groups');
        $repeatPassword = $this->request->getPost('repeatPassword');

        if (count($groups) == 0) {
            $this->flash->error('Please, check at least one group');
            return;
        }

        if ($password != $repeatPassword) {
            $this->flash->error('Passwords are diferent');
            return;
        }

        if (strlen($password) < 6) {
            $this->flash->error('Password is too short!');
            return;
        }

        $user = new Users();
        $user->password = sha1($password);
        $user->name = $name;
        $user->email = $email;
        $user->groups = Groups::find([
            'id IN ({id:array})',
            'bind' => ['id' => $groups]
        ]);

        if ($user->save() == false) {
            foreach ($user->getMessages() as $message) {
                $this->flash->error((string) $message);
            }
        } else {
            $this->flashSession->success('Thanks for sign-up!');
            return $this->forward('users/signin');
        }
    }

    private function registerSession($user)
    {
        $this->session->set('id', $user->id);
        $this->session->set('name', $user->name);
        $groupNames = array_column($user->groups->toArray(), "name");
        $this->session->set('can_edit', array_search(strtolower("AdMIN"), array_map('strtolower', $groupNames)) !== false);
    }

    public function logoutAction()
    {
        $this->session->remove('id');
        return $this->forward('/');
    }
}