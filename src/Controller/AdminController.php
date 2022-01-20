<?php

namespace App\Controller;

class AdminController extends AbstractController
{

    /**
     * Display home page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        $access = $this->isGranted("ROLE_ADMIN");
        if (!$access) {
            $this->addFlash("color-warning", "vous n'avez pas accès à cette page");
            $this->redirectTo("/");
        }
        return $this->twig->render('Admin/index.html.twig');
    }
}
