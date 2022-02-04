<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Model\OfferManager;
use App\Model\AssistManager;

class SlotCalendarService
{
    private AssistManager $assistManager;

    private OfferManager $offerManager;

    private DateTime $dateToCheck;
    private array $availableSlot;
    private array $nameDays;
    private array $nameMonth;

    public function __construct(string $dateTime = "now", array $availlableSlot = [])
    {

        // days of week in frech
        $this->nameDays = [
            '1' => 'Lundi',
            '2' => 'Mardi',
            '3' => 'Mercredi',
            '4' => 'Jeudi',
            '5' => 'Vendredi',
            '6' => 'Samedi',
            '7' => 'Dimanche',
        ];

        // months in french
        $this->nameMonth = [
            '1' => 'Janvier',
            '2' => 'Février',
            '3' => 'Mars',
            '4' => 'Avril',
            '5' => 'Mai',
            '6' => 'Juin',
            '7' => 'Juillet',
            '8' => 'Aout',
            '9' => 'Septembre',
            '10' => 'Octobre',
            '11' => 'Novembre',
            '12' => 'Decembre',
        ];

        if ($dateTime == "now") {
            $this->dateToCheck = new DateTime();
        } else {
            $this->dateToCheck = DateTime::createFromFormat("Y-m-d", $dateTime);
        }
        $this->dateToCheck->setTime(0, 0);
        $this->defineMonday();


        if (!$availlableSlot) {
            $availlableSlot = [
                1 => [
                    [7, 8], [13, 15], [17, 19], [10, 12]
                ],
                2 => [
                    [17, 19], [9, 12], [14, 16]
                ],
                3 => [
                    [14, 16], [10, 12], [17, 19]
                ],
                4 => [
                    [10, 12], [14, 16], [17, 19]
                ],
                5 => [
                    [14, 16], [17, 19], [10, 12]
                ],
                6 => [
                    [10, 12], [14, 16], [17, 20]
                ],
                7 => [
                    [10, 12]
                ],
            ];
            // 1 - 7 are for days in week
            // after, each array give start hour and end hour


            $this->availableSlot = $availlableSlot;
            $this->sortFreeSlots();

            $this->assistManager = new AssistManager();

            $this->offerManager = new OfferManager();
        }
    }

    public function getNavWeeks(string $date = ""): array
    {

        if ($date) {
            $refDateTime = DateTime::createFromFormat("Y-m-d", $date);
            $refDateTime->setTime(0, 0);
            $this->dateToCheck = clone $refDateTime;
        }
        $this->defineMonday();

        $oneWeek = new DateInterval("P7D");

        $previousWeek = clone $this->dateToCheck;
        $previousWeek->sub($oneWeek);

        $nextWeek = clone $this->dateToCheck;
        $nextWeek->add($oneWeek);

        $navWeeks["previousWeek"] = $previousWeek->format("Y-m-d");
        $navWeeks["nextWeek"] = $nextWeek->format("Y-m-d");

        return $navWeeks;
    }

    public function giveAvaillableSlots(int $offerId, int $userId, string $date = ""): array
    {
        if ($date) {
            $refDateTime = DateTime::createFromFormat("Y-m-d", $date);
            $refDateTime->setTime(0, 0);
            $this->dateToCheck = clone $refDateTime;
        }
        // first we must find when slots are already busy
        $this->defineMonday();

        $monday = $this->dateToCheck->format("Y-m-d");
        $toEndWeek = new DateInterval("P6D");
        $endOfWeekDate = clone $this->dateToCheck;
        $endOfWeekDate->add($toEndWeek);
        $sunday = $endOfWeekDate->format("Y-m-d");
        $assists = $this->assistManager->findAllBetween($monday, $sunday);

        // to make a good calendar, we need a min start hour and an max end hour for the week
        $hours = $this->defineStartEndHours();
        $openHour = $hours["earliest"];
        $closeHour = $hours["latest"];
        // now we have to create a calendar without already used slut (for student AND teacher)
        $freeSlots = $this->findSlotWithoutAssist($assists, $userId, $offerId);
        $availlableSlots = $this->convertInCalendar($freeSlots, $openHour, $closeHour, $userId, $offerId);

        return $availlableSlots;
    }

    private function convertInCalendar(array $freeSlots, int $openHour, int $closeHour, int $userId, int $offerId): array
    {
        $refDate = clone $this->dateToCheck;
        $oneDay = new DateInterval('P1D');
        $hours = range($openHour, $closeHour);
        $availlableSlots = [];

        for ($d = 1; $d < 8; $d++) {
            $nameDay = $this->getDay($d);
            $numberDay = $refDate->format("d");
            $nameMonth = $this->getMonth($refDate->format("m"));
            foreach ($hours as $slot) {
                $newSlot = [];
                $newSlot["nameDay"] = $nameDay;
                $newSlot["numberDay"] = $numberDay;
                $newSlot["nameMonth"] = $nameMonth;
                $newSlot["numberMonth"] = $refDate->format("m");
                $newSlot["hour"] = $slot;
                $match = false;
                foreach ($freeSlots as $oneFreeSlot) {
                    $freeSlotToPut = clone $oneFreeSlot;
                    $freeSlotToPutHour = $freeSlotToPut->format("H");
                    $freeSlotToPutDate = clone $freeSlotToPut;
                    $freeSlotToPutDate->setTime(0, 0);

                    if ($freeSlotToPutDate == $refDate && $freeSlotToPutHour == $slot) {
                        $match = true;
                        $dateNow = new DateTime();
                        $dateNow->setTime(0, 0);
                        $dateNow->add($oneDay);
                        if ($refDate < $dateNow) {
                            $match = false;
                        }
                    }
                }
                if ($match) {
                    $newSlot["availlable"] = "availlable";
                    $newSlot["user"] = $userId;
                    $newSlot["offer"] = $offerId;
                    $newSlotDateTime = clone $refDate;
                    // $newSlotDateTime->setTime($slot, 0);
                    $newSlot["date"] = $newSlotDateTime->format("Y-m-d");
                } else {
                    $newSlot["availlable"] = "";
                }
                $availlableSlots[$slot][$d] = $newSlot;
            }
            $refDate->add($oneDay);
        }
        return $availlableSlots;
    }

    private function findSlotWithoutAssist(array $assists, int $userId, int $offerId): array
    {
        $freeSlots = [];
        for ($i = 1; $i < 8; $i++) {
            // the free slots are already sorted
            // now we have to check if at each availlable slot there is not already an other assist
            // user AND teacher must be free at this hour
            $availlableSlotsOfDay = $this->availableSlot[$i];
            $iInDays = new DateInterval('P' . $i - 1 . 'D');
            $addedDaysDateTime = clone $this->dateToCheck;
            $addedDaysDateTime->add($iInDays);
            $dayInCalendar = $addedDaysDateTime->format('d');
            $monthInCalendar = $addedDaysDateTime->format('m');
            foreach ($availlableSlotsOfDay as $slotHours) {
                for ($k = $slotHours[0]; $k < $slotHours[1]; $k++) {
                    $free = true;
                    // three case: 1) no assist at this time, 2) assist but not for this user or teacher, 3) teacher or user already assits to a lesson
                    $addedDaysDateTime->setTime($k, 0);
                    foreach ($assists as $assistToCheck) {
                        $dateOfAssist = DateTime::createFromFormat("Y-m-d H:i:s", $assistToCheck["meet"]);
                        $monthDateOfAssist = $dateOfAssist->format('m');
                        $dayDayOfAssist = $dateOfAssist->format('d');
                        $hourDayOfAssist = $dateOfAssist->format('H');
                        if (
                            $monthDateOfAssist == $monthInCalendar &&
                            $dayDayOfAssist == $dayInCalendar &&
                            $hourDayOfAssist == $k
                        ) {
                            // check if the teacher or the student is in assist
                            if ($assistToCheck["user_id"] == $userId) {
                                $free = false;
                            }
                            if ($free) {
                                $offer = $this->offerManager->selectOneById($offerId);
                                $offerCriteria = [
                                    "teacher_id" => $offer["teacher_id"],
                                ];
                                $offers = $this->offerManager->findBy($offerCriteria);
                                foreach ($offers as $offerToCheck) {
                                    if ($offerToCheck["teacher_id"] == $offer["teacher_id"]) {
                                        if ($offerToCheck["id"] != $offer["id"]) {
                                            $free = false;
                                        }
                                    }
                                }
                            } // else the slot is free at this point
                        }
                    }
                    if ($free) {
                        $freeSlots[] = clone $addedDaysDateTime;
                    }
                }
            }
        }
        return $freeSlots;
    }

    private function defineStartEndHours(): array
    {
        $earliest = 24;
        $latest = 0;
        foreach ($this->availableSlot as $day) {
            foreach ($day as $slotTime) {
                if ($slotTime[0] < $earliest) {
                    $earliest = $slotTime[0];
                }
                if ($slotTime[1] > $latest) {
                    $latest = $slotTime[1];
                }
            }
        }
        return [
            "earliest" => $earliest,
            "latest" => $latest,
        ];
    }

    // private function createSlots(string $date): array
    // {
    //     $dayToDefine = DateTime::createFromFormat("Y-m-d", $date);
    //     $dayOfWeek = $dayOfWeek = $this->dateToCheck->format('N');
    //     $openHours = $this->availableSlot[$dayOfWeek];
    // }


    private function sortFreeSlots()
    {
        for ($j = 1; $j < 8; $j++) {
            $freeSlots = $this->availableSlot[$j];
            do {
                $again = false;
                $elements = count($freeSlots);
                for ($i = 0; $i < $elements - 1; $i++) {
                    if ($freeSlots[$i][0] > $freeSlots[$i + 1][0]) {
                        // switch
                        $tempSlot = $freeSlots[$i + 1];
                        $freeSlots[$i + 1] = $freeSlots[$i];
                        $freeSlots[$i] = $tempSlot;
                        // and restart
                        $again = true;
                    }
                }
            } while ($again);
            $this->availableSlot[$j] = $freeSlots;
        }
    }

    private function defineMonday()
    {
        // $year = $this->dateToCheck->format("Y");
        // $month = $this->dateToCheck->format("m");
        // $day = $this->dateToCheck->format("d");
        // $oneDay = new DateInterval('P1D');
        $dayOfWeek = $this->dateToCheck->format('N');
        $daysToWeekStart = intval($dayOfWeek);
        $daysToWeekStart--;
        $startFirstDayOfWeek = new DateInterval('P' . $daysToWeekStart . 'D');
        $this->dateToCheck->sub($startFirstDayOfWeek);
    }

    private function getMonth(int $month): string
    {
        return $this->nameMonth[$month];
    }

    private function getDay(int $dayOfWeek): string
    {
        return $this->nameDays[$dayOfWeek];
    }
}
