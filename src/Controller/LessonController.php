<?php

namespace App\Controller;

use App\Model\LevelManager;
use App\Model\OfferManager;
use App\Model\LessonManager;
use App\Model\TeacherManager;
use App\Service\UploadService;

/**
 * Class LessonController
 *
 */
class LessonController extends AbstractController
{


    /**
     * Display lesson listing
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $lessonManager = new LessonManager();
        $lessons = $lessonManager->findAllWithLevel();

        return $this->twig->render('Lesson/index.html.twig', ['lessons' => $lessons]);
    }


    /**
     * Display lesson informations specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function show(int $id)
    {
        $lessonManager = new LessonManager();
        $lesson = $lessonManager->findOneWithLevel($id);

        $teachers = $lessonManager->findTeachersforLesson($id);

        return $this->twig->render('Lesson/show.html.twig', [
            'lesson' => $lesson,
            'teachers' => $teachers,
        ]);
    }


    /**
     * Display lesson edition page specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function edit(int $id): string
    {
        $this->isGranted("ROLE_ADMIN", "/");
        $lessonManager = new LessonManager();
        $lesson = $lessonManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_FILES['logo']['name']) {
                $oldLogo = $lesson['logo'] ?? "";
                $uploadService = new UploadService();
                $errorMessage = $uploadService->check($_FILES['logo']);
                if ($errorMessage) {
                    $this->addFlash("color-danger", $errorMessage);
                    $logo = "";
                } else {
                    $logo = $uploadService->update($_FILES['logo'], $oldLogo);
                }
            } else {
                $logo = "";
            }
            $lesson = [
                "name" => $_POST['name'],
                "description" => $_POST['description'],
                "level_id" => $_POST['level_id'],
                "logo" => $logo,
            ];
            if ($lessonManager->edit($id, $lesson)) {
                $this->addFlash("color-success", "le cours a été correctement modifié");
            } else {
                $this->addFlash("color-danger", "il y a eu un problème lors de l'enregistrement du cours");
            }
            $this->redirectTo("/lesson/show/$id");
        }
        $levelManager = new LevelManager();
        $levels = $levelManager->selectAll();
        return $this->twig->render('Lesson/edit.html.twig', [
            'lesson' => $lesson,
            'levels' => $levels,
        ]);
    }


    /**
     * Display lesson creation page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function add()
    {
        $this->isGranted("ROLE_ADMIN", "/");
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonManager = new LessonManager();
            if ($_FILES['logo']['name']) {
                $uploadeService = new UploadService();
                $errorMessage = $uploadeService->check($_FILES['logo']);
                if ($errorMessage) {
                    $this->addFlash("color-danger", $errorMessage);
                    $logo = "";
                } else {
                    $logo = $uploadeService->add($_FILES['logo']);
                }
            } else {
                $logo = "";
            }
            $lesson = [
                "name" => $_POST['name'],
                "description" => $_POST['description'],
                "level_id" => $_POST['level_id'],
                "logo" => $logo,
            ];
            $id = strval($lessonManager->create($lesson));
            $this->redirectTo("/lesson/show/$id");
        }
        $levelManager = new LevelManager();
        $levels = $levelManager->selectAll();
        return $this->twig->render('Lesson/add.html.twig', [
            'levels' => $levels,
        ]);
    }


    /**
     * Handle lesson deletion
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->isGranted("ROLE_ADMIN", "/");
        $lessonManager = new LessonManager();
        $uploadeService = new UploadService();
        $lesson = $lessonManager->selectOneById($id);
        if ($lesson['logo']) {
            $uploadeService->delete($lesson['logo']);
        }
        $lessonManager->delete($id);
        $this->addFlash("color-success", "le niveau a été correctement supprimer");
        $this->redirectTo('/lesson/index');
    }

    public function addteacher(int $lessonId = 0)
    {
        $this->isGranted("ROLE_ADMIN", "/lesson");
        $lessonManager = new LessonManager();
        $offer = [
            "teacher_id" => "",
            "lesson_id" => $lessonId,
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = false;
            if (!$_POST["teacher_id"]) {
                $error = true;
                $this->addFlash("color-danger", "il est obligatoire de choisir un prof");
            }
            $lesson = $lessonManager->selectOneById($_POST["lesson_id"]);
            if (!$lesson) {
                $error = true;
                $this->addFlash("color-danger", "il y a un problème avec l'enregistrement de la lesson, veuillez recommencer la procédure");
                $this->redirectTo("/lesson");
            }
            if (!$error) {
                $lessonId = $_POST["lesson_id"];
                $offer = [
                    "teacher_id" => $_POST["teacher_id"],
                    "lesson_id" => $_POST["lesson_id"],
                ];
                $offerManager = new OfferManager();
                if ($offerManager->create($offer)) {
                    $this->addFlash("color-success", "le prof a été correctement assignée");
                    $this->redirectTo("/lesson/show/$lessonId");
                }
            }
        }
        $lesson = $lessonManager->selectOneById($lessonId);
        $teacherManager = new TeacherManager();
        $teachers = $teacherManager->findAllWithUser();
        return $this->twig->render('Lesson/addteacher.html.twig', [
            "offer" => $offer,
            "teachers" => $teachers,
            "lesson" => $lesson,
        ]);
    }

    public function delteacher(int $lessonId = 0)
    {
        $this->isGranted("ROLE_ADMIN", "/lesson");
        $lessonManager = new LessonManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = false;
            if (!$_POST["offer_id"]) {
                $error = true;
                $this->addFlash("color-danger", "il est obligatoire de choisir un prof");
            }
            $lesson = $lessonManager->selectOneById($_POST["lesson_id"]);
            if (!$lesson) {
                $error = true;
                $this->addFlash("color-danger", "il y a un problème avec l'identification du cours, veuillez recommencer la procédure");
                $this->redirectTo("/lesson");
            }
            if (!$error) {
                $offerManager = new OfferManager();
                if ($offerManager->delete($_POST["offer_id"])) {
                    $this->addFlash("color-success", "le prof a été correctement retiré");
                } else {
                    $this->addFlash("color-danger", "il y a eu un problème lors du déassignation du prof");
                }

                $this->redirectTo("/lesson/show/$lessonId");
            }
        }
        $lesson = $lessonManager->selectOneById($lessonId);

        $teachers = $lessonManager->findTeachersforLesson($lessonId);
        return $this->twig->render('Lesson/delteacher.html.twig', [
            "teachers" => $teachers,
            "lesson" => $lesson,
        ]);
    }
}
