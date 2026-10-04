<?php

// Class used to represent an application user.
class User {

    private $userId;
    private $email;
    private $password;
    private $userLevel;

    public function __construct(
        $email,
        $password,
        $userLevel,
        $userId = null
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->userLevel = $userLevel;
        $this->userId = $userId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($value) {
        $this->userId = $value;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($value) {
        $this->email = $value;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($value) {
        $this->password = $value;
    }

    public function getUserLevel() {
        return $this->userLevel;
    }

    public function setUserLevel($value) {
        $this->userLevel = $value;
    }
}