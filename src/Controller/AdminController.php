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
        $this->isGranted("ROLE_ADMIN", "/");
        return $this->twig->render('Admin/index.html.twig');
    }
}
