<?php

namespace App\Controller;

use App\Model\LessonManager;
use App\Model\LevelManager;
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
        $lessons = $lessonManager->selectAll();

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
        $lesson = $lessonManager->selectOneById($id);

        return $this->twig->render('Lesson/show.html.twig', ['lesson' => $lesson]);
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
                "lesson" => $_POST['lesson'],
                "description" => $_POST['description'],
                "logo" => $logo,
            ];
            if ($lessonManager->edit($id, $lesson)) {
                $this->addFlash("color-success", "le niveau a été correctement modifié");
            } else {
                $this->addFlash("color-danger", "il y a eu un problème lors de l'enregistrement du fichier");
            }
            $this->redirectTo("/lesson");
        }

        return $this->twig->render('Lesson/edit.html.twig', ['lesson' => $lesson]);
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            var_dump($_POST);
            die;
            $lessonManager = new LessonManager();
            if ($_FILES['logo']) {
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
}
