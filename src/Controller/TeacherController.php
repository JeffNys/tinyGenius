<?php

namespace App\Controller;

use App\Model\TeacherManager;
use App\Model\UserManager;
use App\Service\UploadService;

/**
 * Class TeacherController
 *
 */
class TeacherController extends AbstractController
{


    /**
     * Display teacher listing
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $teacherManager = new TeacherManager();
        $teachers = $teacherManager->selectAll();

        return $this->twig->render('Teacher/index.html.twig', ['teachers' => $teachers]);
    }

    public function list()
    {
        $teacherManager = new TeacherManager();
        $teachers = $teacherManager->findAllWithUser();

        return $this->twig->render('Teacher/list.html.twig', ['teachers' => $teachers]);
    }


    /**
     * Display teacher informations specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function show(int $id)
    {
        $teacherManager = new TeacherManager();
        $teacher = $teacherManager->findOneWithUser($id);

        return $this->twig->render('Teacher/show.html.twig', ['teacher' => $teacher]);
    }


    /**
     * Display teacher edition page specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function edit(int $id): string
    {
        $teacherManager = new TeacherManager();
        $teacher = $teacherManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldUserID = $teacher["user_id"];
            $error = false;
            if (!$_POST['title']) {
                $this->addFlash("color-danger", "le prseudo est obligatoire");
                $error = true;
            }
            if (!$_POST['user_id']) {
                $this->addFlash("color-danger", "le vous devez selectionner le compte du prof");
                $error = true;
            }
            if ($_FILES['image']['name']) {
                $uploadeService = new UploadService();
                $errorMessage = $uploadeService->check($_FILES['image']);
                if ($errorMessage) {
                    $this->addFlash("color-danger", $errorMessage);
                    $error = true;
                    $image = "";
                } else {
                    $image = $uploadeService->add($_FILES['image']);
                }
            } else {
                $image = "";
            }
            if (!$error) {
                $userId = intval($_POST["user_id"]);
                $teacher = [
                    "title" => $_POST['title'],
                    "description" => $_POST['description'],
                    "user_id" => $userId,
                    "image" => $image,
                ];
                if ($teacherManager->edit($id, $teacher)) {
                    if ($userId != $oldUserID) {
                        $userManager = new UserManager();
                        $user = $userManager->selectOneById($userId);
                        $role = json_decode($user["role"]);
                        array_push($role, "ROLE_TEACHER");
                        $userRole = ["role" => json_encode($role)];
                        $userManager->edit($userId, $userRole);
                        $oldUser = $userManager->selectOneById($oldUserID);
                        $oldUserRole = json_decode($oldUser["role"]);
                        $oldUserNewRole = [];
                        foreach ($oldUserRole as $oldRole) {
                            if ($oldRole != "ROLE_TEACHER") {
                                $oldUserNewRole[] = $oldRole;
                            }
                        }
                        $oldUserRole = ["role" => json_encode($oldUserNewRole)];
                        $userManager->edit($oldUserID, $oldUserRole);
                    }
                    $this->addFlash("color-success", "le niveau a été correctement modifié");
                    $this->redirectTo("/teacher/show/$id");
                } else {
                    $this->addFlash("color-danger", "il y a eu un problème lors de l'enregistrement du fichier");
                }
            }
        }
        $userManager = new UserManager();
        $users = $userManager->selectAllOrdered();
        return $this->twig->render('Teacher/edit.html.twig', [
            'teacher' => $teacher,
            'users' => $users,
        ]);
    }


    /**
     * Display teacher creation page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function add()
    {
        $teacher = [
            "title" => "",
            "description" => "",
            "user_id" => 0,
            "image" => "",
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teacherManager = new TeacherManager();
            $error = false;
            if (!$_POST['title']) {
                $this->addFlash("color-danger", "le prseudo est obligatoire");
                $error = true;
            }
            if (!$_POST['user_id']) {
                $this->addFlash("color-danger", "le vous devez selectionner le compte du prof");
                $error = true;
            }
            if ($_FILES['image']['name']) {
                $uploadeService = new UploadService();
                $errorMessage = $uploadeService->check($_FILES['image']);
                if ($errorMessage) {
                    $this->addFlash("color-danger", $errorMessage);
                    $error = true;
                    $image = "";
                } else {
                    $image = $uploadeService->add($_FILES['image']);
                }
            } else {
                $image = "";
            }
            if (!$error) {
                $userId = intval($_POST["user_id"]);
                    $teacher = [
                    "title" => $_POST['title'],
                    "description" => $_POST['description'],
                    "user_id" => $userId,
                    "image" => $image,
                ];
                $id = strval($teacherManager->create($teacher));
                $userManager = new UserManager();
                $user = $userManager->selectOneById($userId);
                $role = json_decode($user["role"]);
                array_push($role, "ROLE_TEACHER");
                $userRole = ["role" => json_encode($role)];
                $userManager->edit($userId, $userRole);
                $this->addFlash("color-success", "le profil du prof a été correctement créé");
                $this->redirectTo("/teacher/show/$id");
            }
            
        }


        $userManager = new UserManager();
        $users = $userManager->selectAllOrdered();
        return $this->twig->render('Teacher/add.html.twig', [
            "users" => $users,
            "teacher" => $teacher,
        ]);
    }


    /**
     * Handle teacher deletion
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $teacherManager = new TeacherManager();
        $uploadeService = new UploadService();
        $teacher = $teacherManager->selectOneById($id);
        if ($teacher['image']) {
            $uploadeService->delete($teacher['image']);
        }
        $teacherManager->delete($id);
        $this->addFlash("color-success", "le niveau a été correctement supprimer");
        $this->redirectTo('/teacher/list');
    }
}
