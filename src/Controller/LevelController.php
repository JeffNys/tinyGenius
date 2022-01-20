<?php

namespace App\Controller;

use App\Model\LevelManager;
use App\Service\UploadService;

/**
 * Class LevelController
 *
 */
class LevelController extends AbstractController
{


    /**
     * Display level listing
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $levelManager = new LevelManager();
        $levels = $levelManager->selectAll();

        return $this->twig->render('Level/index.html.twig', ['levels' => $levels]);
    }


    /**
     * Display level informations specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function show(int $id)
    {
        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById($id);

        return $this->twig->render('Level/show.html.twig', ['level' => $level]);
    }


    /**
     * Display level edition page specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function edit(int $id): string
    {
        $levelManager = new LevelManager();
        $level = $levelManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_FILES['logo']['name']) {
                $oldLogo = $level['logo'] ?? "";
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
            $level = [
                "level" => $_POST['level'],
                "description" => $_POST['description'],
                "logo" => $logo,
            ];
            if ($levelManager->edit($id, $level)) {
                $this->addFlash("color-success", "le niveau a été correctement modifié");
            } else {
                $this->addFlash("color-danger", "il y a eu un problème lors de l'enregistrement du fichier");
            }
            $this->redirectTo("/level");
        }

        return $this->twig->render('Level/edit.html.twig', ['level' => $level]);
    }


    /**
     * Display level creation page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function add()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $levelManager = new LevelManager();
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
            $level = [
                "level" => $_POST['level'],
                "description" => $_POST['description'],
                "logo" => $logo,
            ];
            $id = strval($levelManager->create($level));
            $this->redirectTo("/level/show/$id");
        }

        return $this->twig->render('Level/add.html.twig');
    }


    /**
     * Handle level deletion
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        $levelManager = new LevelManager();
        $uploadeService = new UploadService();
        $level = $levelManager->selectOneById($id);
        if ($level['logo']) {
            $uploadeService->delete($level['logo']);
        }
        $levelManager->delete($id);
        $this->addFlash("color-success", "le niveau a été correctement supprimer");
        $this->redirectTo('/level/index');
    }
}
