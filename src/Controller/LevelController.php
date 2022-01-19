<?php

namespace App\Controller;

use App\Model\LevelManager;

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
            $level = [
                "level" => $_POST['level'],
                "description" => $_POST['description'],
                "logo" => $_POST['logo'],
            ];
            $levelManager->edit($id, $level);
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
            $level = [
                "level" => $_POST['level'],
                "description" => $_POST['description'],
                "logo" => $_POST['logo'],
            ];
            $id = $levelManager->create($level);
            header('Location:/level/show/' . $id);
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
        $levelManager->delete($id);
        header('Location:/level/index');
    }
}
