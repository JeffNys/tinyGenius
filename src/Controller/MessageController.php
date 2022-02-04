<?php

namespace App\Controller;

use App\Model\MessageManager;
use App\Model\UserManager;
use App\Service\UploadService;
use DateTime;

/**
 * Class MessageController
 *
 */
class MessageController extends AbstractController
{


    public function index(int $id)
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->isGranted("ROLE_TEACHER", "/");
        }
        $messageManager = new MessageManager();
        $messages = $messageManager->selectAll();

        return $this->twig->render('Message/index.html.twig', ['messages' => $messages]);
    }


    /**
     * Display message informations specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function show(int $id)
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->isGranted("ROLE_TEACHER", "/");
        }
        $messageManager = new MessageManager();
        $message = $messageManager->selectOneById($id);
        $idUser = $message["user_id"];
        $userManager = new UserManager();
        $user = $userManager->selectOneById($idUser);

        return $this->twig->render('Message/show.html.twig', [
            'message' => $message,
            'user' => $user,
        ]);
    }


    /**
     * Display message edition page specified by $id
     *
     * @param int $id
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function edit(int $id): string
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->isGranted("ROLE_TEACHER", "/");
        }
        $messageManager = new MessageManager();
        $message = $messageManager->selectOneById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $messageManager = new MessageManager();
            $date = new DateTime();
            $message = [
                "user_id" => $_POST['user_id'],
                "title" => $_POST['title'],
                "message" => $_POST['message'],
            ];
            if ($messageManager->edit($id, $message)) {
                $this->addFlash("color-success", "le message a été correctement modifié");
            } else {
                $this->addFlash("color-danger", "il y a eu un problème lors de l'enregistrement du message");
            }
            $this->redirectTo("/user/forteacher/{$_POST['user_id']}");
        }

        return $this->twig->render('Message/edit.html.twig', ['message' => $message]);
    }


    /**
     * Display message creation page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function add(int $id = 0)
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->isGranted("ROLE_TEACHER", "/");
        }
        $message = [
            "title" => "",
            "message" => "",
        ];
        if ($id) {
            $message["user_id"] = $id;
        } else {
            if (!$_POST ?? false) {
                $this->addFlash("color-info", "problème de site, veuillez prendre contact avec l'administrateur");
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $messageManager = new MessageManager();
            $date = new DateTime();
            $message = [
                "user_id" => $_POST['user_id'],
                "title" => $_POST['title'],
                "message" => $_POST['message'],
                "message_date" => $date->format("Y-m-d"),
            ];
            $id = strval($messageManager->create($message));
            $this->redirectTo("/user/forteacher/$id");
        }

        return $this->twig->render('Message/add.html.twig', [
            "message" => $message,
        ]);
    }


    /**
     * Handle message deletion
     *
     * @param int $id
     */
    public function delete(int $id)
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            $this->isGranted("ROLE_TEACHER", "/");
        }
        $messageManager = new MessageManager();
        $message = $messageManager->selectOneById($id);
        $userId = $message['user_id'];
        $messageManager->delete($id);
        $this->addFlash("color-success", "le message a été correctement supprimé");
        $this->redirectTo("/user/forteacher/$userId");
    }
}
