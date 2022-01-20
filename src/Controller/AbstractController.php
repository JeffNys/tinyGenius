<?php

namespace App\Controller;

use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

abstract class AbstractController
{
    /**
     * @var Environment
     */
    protected $twig;


    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        $loader = new FilesystemLoader(APP_VIEW_PATH);
        $this->twig = new Environment(
            $loader,
            [
                'cache' => !APP_DEV,
                'debug' => APP_DEV,
            ]
        );
        $this->twig->addExtension(new DebugExtension());

        if (isset($_SESSION["user"])) {
            $this->twig->addGlobal("appUser", $_SESSION["user"]);
        }
        $getFlash = new TwigFunction('getFlash', function () {
            $messages = [];
            if (isset($_SESSION['flash'])) {
                // clear messages after first read
                $messages = $_SESSION['flash'];
                unset($_SESSION['flash']);
            }
            return $messages;
        });
        $this->twig->addFunction($getFlash);
    }

    public function addFlash(string $color, string $message): void
    {
        $_SESSION['flash'] = $_SESSION['flash'] ?? [];
        array_push($_SESSION['flash'], [
            "color" => $color,
            "message" => $message,
        ]);
    }


    public function redirectTo(string $route): void
    {
        header("Location: $route");
        exit;
    }
}
