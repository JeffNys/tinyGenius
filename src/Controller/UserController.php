<?php

namespace App\Controller;

use App\Model\UserManager;
use App\Service\UploadService;

/**
 * Class UserController
 *
 */
class UserController extends AbstractController
{

    public function index()
    {
        $this->redirectTo("/user/login");
    }
    /**
     * Display user listing
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = false;
            if (!$_POST['email']) {
                $this->addFlash("color-danger", "une adresse de courriel est obligatoire");
                $error = true;
            }
            if (!$_POST['password']) {
                $this->addFlash("color-danger", "vous devez rentrer un mot de passe");
                $error = true;
            }
            if (!$error) {
                $userManager = new UserManager();
                $criteria = [
                    "email" => $_POST["email"],
                ];
                $userTab = $userManager->findBy($criteria);
                $user = $userTab[0];
                if ($user ?? false) {
                    $passOk = password_verify($_POST['password'], $user["password"]);
                    if ($passOk) {
                        // connexion
                        $_SESSION['user'] = $user;
                        unset($_SESSION['user']['password']);
                        $_SESSION['user']['role'] = json_decode($user['role']);
                        $this->addFlash("color-success", "vous êtes connecté");
                        $this->redirectTo("/");
                    }
                }
                $this->addFlash("color-danger", "erreur de mot de passe ou de courriel");
            }
            
        }

        return $this->twig->render('User/login.html.twig');
    }

    public function logout()
    {
        unset($_SESSION["user"]);
        $this->addFlash("color-success", "vous êtes correctement déconnecté");
        $this->redirectTo("/");
    }


    /**
     * Display user informations specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function profile()
    {
        $id = $_SESSION['user']['id'];
        $userManager = new UserManager();
        $user = $userManager->selectOneById($id);

        return $this->twig->render('User/profile.html.twig', ['user' => $user]);
    }


    /**
     * Display user edition page specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function edit(): string
    {
        $id = $_SESSION["user"]["id"];
        $userManager = new UserManager();
        $user = $userManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userManager = new UserManager();
            $error = false;
            if (!$_POST['firstname']) {
                $this->addFlash("color-danger", "le prénom est obligatoire");
                $error = true;
            }
            if (!$_POST['lastname']) {
                $this->addFlash("color-danger", "le nom est obligatoire");
                $error = true;
            }
            if (!$_POST['email']) {
                $this->addFlash("color-danger", "une adresse de courriel est obligatoire");
                $error = true;
            }
            if (!$_POST['birthday']) {
                $this->addFlash("color-danger", "la date d'anniversaire est obligatoire");
                $error = true;
            }
            $criteria = [
                "email" => $_POST['email'],
            ];
            $testEmail = $userManager->findBy($criteria);
            if ($testEmail[0]['id'] != $id) {
                $this->addFlash("color-danger", "Un compte est déjà enregistré avec cette adresse de courriel");
                $error = true;
            }
            $user = [
                "firstname" => $_POST['firstname'],
                "lastname" => $_POST['lastname'],
                "email" => $_POST['email'],
                "phone" => $_POST['phone'],
                "validate" => $_SESSION['user']['validate'],
                "role" => json_encode($_SESSION['user']['role']),
                "birthday" => $_POST['birthday'],
            ];
            
            if (!$error) {
                $password = $_POST["password"];
                if ($password) {
                    $user['password'] = password_hash($password, PASSWORD_ARGON2ID);
                }
                $userManager->edit($id, $user);
                $this->redirectTo("/user/profile");
            }
        }

        return $this->twig->render('User/edit.html.twig', ['user' => $user]);
    }


    /**
     * Display user creation page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function subscribe()
    {
        $user = [
            "firstname" => "",
            "lastname" => "",
            "email" => "",
            "password" => "",
            "phone" => "",
            "validate" => 0,
            "birthday" => "",
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userManager = new UserManager();
            $error = false;
            if (!$_POST['firstname']) {
                $this->addFlash("color-danger", "le prénom est obligatoire");
                $error = true;
            }
            if (!$_POST['lastname']) {
                $this->addFlash("color-danger", "le nom est obligatoire");
                $error = true;
            }
            if (!$_POST['email']) {
                $this->addFlash("color-danger", "une adresse de courriel est obligatoire");
                $error = true;
            }
            if (!$_POST['password']) {
                $this->addFlash("color-danger", "vous devez rentrer un mot de passe");
                $error = true;
            }
            if (!$_POST['birthday']) {
                $this->addFlash("color-danger", "la date d'anniversaire est obligatoire");
                $error = true;
            }
            $criteria = [
                "email" => $_POST['email'],
            ];
            $testEmail = $userManager->findBy($criteria);
            if ($testEmail) {
                $this->addFlash("color-danger", "Un compte est déjà enregistré avec cette adresse de courriel");
                $error = true;
            }
            $user = [
                "firstname" => $_POST['firstname'],
                "lastname" => $_POST['lastname'],
                "email" => $_POST['email'],
                "password" => $_POST['password'],
                "phone" => $_POST['phone'],
                "validate" => 0,
                "role" => json_encode(["ROLE_USER"]),
                "birthday" => $_POST['birthday'],
            ];
            
            if (!$error) {
                // TO DO hash password
                $user['password'] = password_hash($_POST['password'], PASSWORD_ARGON2ID);
                $userManager->create($user);
                $this->addFlash("color-success", "votre compte a été créé");
                $this->redirectTo("/user/login");
            }
            
        }

        return $this->twig->render('User/subscribe.html.twig', [
            'user' => $user,
        ] );
    }


    /**
     * Handle user deletion
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $userManager = new UserManager();
        $uploadeService = new UploadService();
        $user = $userManager->selectOneById($id);
        if ($user['logo']) {
            $uploadeService->delete($user['logo']);
        }
        $userManager->delete($id);
        $this->addFlash("color-success", "le niveau a été correctement supprimer");
        $this->redirectTo('/user/index');
    }
}
