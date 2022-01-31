<?php

namespace App\Service;

use App\Model\TeacherManager;
use App\Model\UserManager;

class TeacherRoleService
{
    public function addTeacherRole(int $userId): bool
    {
        $finish = false;

        $userManager = new UserManager();
        $user = $userManager->selectOneById($userId);
        if ($user) {
            if (!$this->checkTeacherRole($userId)) {
                $role = json_decode($user["role"]);
                array_push($role, "ROLE_TEACHER");
                $userRole = ["role" => json_encode($role)];
                $userManager->edit($userId, $userRole);
            }
            $finish = true;
        }
        return $finish;
    }

    public function checkTeacherRole(int $userId): bool
    {
        $itIsTeacher = false;

        $userManager = new UserManager();
        $user = $userManager->selectOneById($userId);
        if ($user) {
            $role = json_decode($user["role"]);
            if (in_array("ROLE_TEACHER", $role)) {
                $itIsTeacher = true;
            }
        }
        return $itIsTeacher;
    }

    public function deleteTeacherRole(int $userId): int
    {
        $userManager = new UserManager();
        $teacherManager = new TeacherManager();
        $user = $userManager->selectOneById($userId);
        if (!$user) {
            $userId = 0;
        } else {
            $teacherIDCriteria = [
                "user_id" => $userId,
            ];
            $teacherTab = $teacherManager->findBy($teacherIDCriteria);
            if (!$teacherTab) {
                $userRole = json_decode($user["role"]);
                $userNewRole = [];
                foreach ($userRole as $oldRole) {
                    if ($oldRole != "ROLE_TEACHER") {
                        $userNewRole[] = $oldRole;
                    }
                }
                $userRole = ["role" => json_encode($userNewRole)];
                $userManager->edit($userId, $userRole);
                $userId = 0; // this user is no longer a teacher
            }
        }
        return $userId;
    }
}
