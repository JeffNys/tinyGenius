<?php

namespace App\Controller;

use App\Model\LessonManager;
use App\Model\OfferManager;
use App\Model\UserManager;
use App\Model\TeacherManager;
use App\Service\UploadService;
use App\Service\TeacherRoleService;

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
        $teachers = $teacherManager->findAllWithUser();

        return $this->twig->render('Teacher/index.html.twig', ['teachers' => $teachers]);
    }

    public function list()
    {
        $this->isGranted("ROLE_ADMIN", "/");
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
        if (!$teacher) {
            $this->addFlash("color-warning", "il n'y a pas de prof a cette adresse");
            if ($this->isGranted("ROLE_ADMIN")) {
                $this->redirectTo("/teacher/list");
            } else {
                $this->redirectTo("/teacher");
            }
        }

        $lessons = $teacherManager->findLessonsforTeacher($id);
        return $this->twig->render('Teacher/show.html.twig', [
            'teacher' => $teacher,
            'lessons' => $lessons,
        ]);
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
                        $teacherRoleService = new TeacherRoleService();
                        $userManager = new UserManager();
                        $oldUser = $userManager->selectOneById($oldUserID);
                        if ($teacherRoleService->deleteTeacherRole($oldUserID)) {
                            $this->addFlash("color-success", "l'utilisateur {$oldUser['firstname']} {$oldUser['lastname']} reste un prof (depuis un autre profil de prof");
                        } else {
                            $this->addFlash("color-success", "l'utilisateur {$oldUser['firstname']} {$oldUser['lastname']} n'est plus un prof");
                        }

                        $user = $userManager->selectOneById($userId);
                        if ($teacherRoleService->addTeacherRole($userId)) {
                            $this->addFlash("color-success", "l'utilisateur {$user['firstname']} {$user['lastname']} est un prof");
                        } else {
                            $this->addFlash("color-danger", "il y a eu un problème lors de la mise à jours de l'utilisateur {$user['firstname']} {$user['lastname']}");
                        }
                    }
                    $this->addFlash("color-success", "le prof a été correctement modifié");
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
                $teacherRoleService = new TeacherRoleService();
                if (!$teacherRoleService->addTeacherRole($userId)) {
                    $this->addFlash("color-warning", "il y a eu un problème lors de la mise à jour de l'utilisateur");
                } else {
                    $this->addFlash("color-success", "l'utilisateur est enregistré en tant que prof");
                }
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
        $userId = $teacher["user_id"];

        $teacherManager->delete($id);
        $this->addFlash("color-success", "le profil de profs a été correctement supprimer");

        $teacherRoleService = new TeacherRoleService();
        if ($teacherRoleService->deleteTeacherRole($userId)) {
            $this->addFlash("color-success", "l'utilisateur est encore un prof (avec un autre profil de prof)");
        } else {
            $this->addFlash("color-warning", "l'utilisateur n'est plus un prof");
        }

        $this->redirectTo('/teacher/list');
    }

    public function addlesson(int $teacherId = 0)
    {
        $this->isGranted("ROLE_ADMIN", "/teacher");
        $teacherManager = new TeacherManager();
        $offer = [
            "teacher_id" => $teacherId,
            "lesson_id" => "",
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = false;
            if (!$_POST["lesson_id"]) {
                $error = true;
                $this->addFlash("color-danger", "il est obligatoire de choisir une lesson");
            }
            $teacher = $teacherManager->selectOneById($_POST["teacher_id"]);
            if (!$teacher) {
                $error = true;
                $this->addFlash("color-danger", "il y a un problème avec l'enregistrement du prof, veuillez recommencer la procédure");
                $this->redirectTo("/teacher");
            }
            if (!$error) {
                $teacherId = $_POST["teacher_id"];
                $offer = [
                    "teacher_id" => $_POST["teacher_id"],
                    "lesson_id" => $_POST["lesson_id"],
                ];
                $offerManager = new OfferManager();
                if ($offerManager->create($offer)) {
                    $this->addFlash("color-success", "la lesson a été correctement assignée");
                    $this->redirectTo("/teacher/show/$teacherId");
                }
            }
        }
        $teacher = $teacherManager->selectOneById($teacherId);
        $lessonManager = new LessonManager();
        $lessons = $lessonManager->selectAll();
        return $this->twig->render('Teacher/addlesson.html.twig', [
            "offer" => $offer,
            "teacher" => $teacher,
            "lessons" => $lessons,
        ]);
    }

    public function dellesson(int $teacherId = 0)
    {
        $this->isGranted("ROLE_ADMIN", "/teacher");
        $teacherManager = new TeacherManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = false;
            if (!$_POST["offer_id"]) {
                $error = true;
                $this->addFlash("color-danger", "il est obligatoire de choisir une lesson");
            }
            $teacher = $teacherManager->selectOneById($_POST["teacher_id"]);
            if (!$teacher) {
                $error = true;
                $this->addFlash("color-danger", "il y a un problème avec l'identification du prof, veuillez recommencer la procédure");
                $this->redirectTo("/teacher");
            }
            if (!$error) {
                $offerManager = new OfferManager();
                if ($offerManager->delete($_POST["offer_id"])) {
                    $this->addFlash("color-success", "le cours a été correctement retiré");
                } else {
                    $this->addFlash("color-danger", "il y a eu un problème lors du déassignation du cours");
                }

                $this->redirectTo("/teacher/show/$teacherId");
            }
        }
        $teacher = $teacherManager->selectOneById($teacherId);

        $lessons = $teacherManager->findLessonsforTeacher($teacherId);
        return $this->twig->render('Teacher/dellesson.html.twig', [
            "teacher" => $teacher,
            "lessons" => $lessons,
        ]);
    }
}
