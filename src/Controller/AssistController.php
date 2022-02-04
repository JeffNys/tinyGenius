<?php

namespace App\Controller;

use App\Model\AssistManager;
use App\Service\SlotCalendarService;
use DateTime;

class AssistController extends AbstractController
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
        return $this->twig->render('Assist/index.html.twig');
    }

    public function add(int $offer, string $date = "")
    {
        if (!$this->isGranted("ROLE_USER")) {
            $this->addFlash("color-warning", "vous devez être connecté pour acceder à cette page");
            $this->redirectTo("/user/login");
        }
        $slotCalendarService = new SlotCalendarService();
        $calendar = $slotCalendarService->giveAvaillableSlots($offer, $_SESSION["user"]["id"], $date);
        $navWeeks = $slotCalendarService->getNavWeeks($date);

        return $this->twig->render('Assist/add.html.twig', [
            'calendar' => $calendar,
            'navWeeks' => $navWeeks,
            'offer' => $offer,
        ]);
    }

    public function register(string $date, int $hour, int $user, int $offer)
    {
        if (!$date || !$hour || !$user || !$offer || !$this->isGranted("ROLE_USER")) {
            $this->addFlash("color-danger", "il y a eu un problème d'enregistrement");
            $this->redirectTo("/");
        }
        if ($user != $_SESSION["user"]["id"]) {
            $this->addFlash("color-danger", "il y a eu une incohérence d'enregistrement");
            $this->redirectTo("/");
        }

        $meet = DateTime::createFromFormat("Y-m-d", $date);
        $meet->setTime($hour, 0);
        $assist = [
            "user_id" => $user,
            "offer_id" => $offer,
            "meet" => $meet->format("Y-m-d H:i:s"),
        ];
        $assistManager = new AssistManager();
        if ($assistManager->create($assist)) {
            $this->addFlash("color-success", "votre rendez vous a été pris");
        } else {
            $this->addFlash("color-danger", "il y a eu un problème d'enregistrement du rendez-vous");
        }
        $this->redirectTo("/");
    }
}
