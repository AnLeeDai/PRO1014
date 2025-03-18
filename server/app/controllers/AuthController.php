<?php
require_once __DIR__ . "/../models/AuthModel.php";

class AuthController
{
    // register user controller
    public function handleRegister(): void
    {
        // get data from request
        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $password_confirm = $data['password_confirm'] ?? '';
        $full_name = $data['full_name'] ?? '';
        $email = $data['email'] ?? '';
        $phone_number = $data['phone_number'] ?? '';
        $address = $data['address'] ?? '';

        // validate input not null
        if (empty($username)) {
            echo json_encode(["success" => false, "message" => "Username is required"]);
            exit();
        } elseif (empty($password)) {
            echo json_encode(["success" => false, "message" => "Password is required"]);
            exit();
        } elseif (empty($full_name)) {
            echo json_encode(["success" => false, "message" => "Full name is required"]);
            exit();
        }

        // validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email address"]);
            exit();
        }

        // verify phone number in Vietnamese format (optional) && minimum 10 characters
        if (
            !empty($phone_number) &&
            (!preg_match('/^0[0-9]{9,10}$/', $phone_number) || strlen($phone_number) < 10)
        ) {
            echo json_encode(["success" => false, "message" => "Invalid phone number"]);
            exit();
        }

        // validate password confirm
        if ($password !== $password_confirm) {
            echo json_encode(["success" => false, "message" => "Password confirm not match"]);
            exit();
        }

        // hash password
        $password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

        // add user to db and show notification
        $authModel = new AuthModel();
        $result = $authModel->register(
            $username,
            $password,
            $full_name,
            $email,
            $phone_number,
            $address
        );

        if ($result['success'] === false) {
            http_response_code(400);
        } else {
            http_response_code(200);
        }
        echo json_encode($result);
    }

    // login user controller
    public function handleLogin(): void
    {
        // get data from request
        $data = json_decode(file_get_contents("php://input"), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // validate input not null
        if (empty($username)) {
            echo json_encode(["success" => false, "message" => "Username is required"]);
            exit();
        } elseif (empty($password)) {
            echo json_encode(["success" => false, "message" => "Password is required"]);
            exit();
        }

        // generate token for user
        $generateToken = bin2hex(random_bytes(50));

        // get user from db and show notification
        $authModel = new AuthModel();
        $result = $authModel->login($username, $password, $generateToken);

        if ($result['success'] === false) {
            http_response_code(400);
        } else {
            http_response_code(200);
        }
        echo json_encode($result);
    }
}