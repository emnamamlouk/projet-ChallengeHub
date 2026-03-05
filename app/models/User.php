<?php

require_once __DIR__ . '/../../config/Database.php';

class User {

    private $id;
    private $username;
    private $email;
    private $password;
    private $bio;
    private $avatar;
    private $created_at;

    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function register($username, $email, $password) {

        try {

            $query = "SELECT id FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return "Cet email est déjà utilisé";
            }

            $query = "SELECT id FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return "Ce nom d'utilisateur est déjà pris";
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, email, password) 
                      VALUES (:username, :email, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);

            return $stmt->execute();

        } catch (PDOException $e) {
            return false;
        }
    }

    public function login($email, $password) {

        try {

            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {

                $user = $stmt->fetch();

                if (password_verify($password, $user['password'])) {

                    $this->id = $user['id'];
                    $this->username = $user['username'];
                    $this->email = $user['email'];
                    $this->bio = $user['bio'];
                    $this->avatar = $user['avatar'];
                    $this->created_at = $user['created_at'];

                    return true;
                }

                return "Mot de passe incorrect";
            }

            return "Aucun compte trouvé avec cet email";

        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateProfile($id, $username, $bio, $avatar = null) {

        try {

            if ($avatar) {
                $query = "UPDATE users 
                          SET username = :username, bio = :bio, avatar = :avatar 
                          WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':avatar', $avatar);
            } else {
                $query = "UPDATE users 
                          SET username = :username, bio = :bio 
                          WHERE id = :id";
                $stmt = $this->conn->prepare($query);
            }

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':bio', $bio);
            $stmt->bindParam(':id', $id);

            return $stmt->execute();

        } catch (PDOException $e) {
            return false;
        }
    }

    public function deleteAccount($id) {

        try {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUserById($id) {

        try {
    $query = "SELECT id, username, email, bio, avatar, role, created_at 
              FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
public function searchUsers($search) {
    try {
        $query = "SELECT id, username, avatar, bio, created_at 
                  FROM users 
                  WHERE username LIKE :search 
                  ORDER BY username ASC 
                  LIMIT 20";
        
        $stmt = $this->conn->prepare($query);
        $searchTerm = "%$search%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getBio() { return $this->bio; }
    public function getAvatar() { return $this->avatar; }
}