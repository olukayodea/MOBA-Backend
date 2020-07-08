<?php
if ($token == "") {
        if (isset($_SESSION['users'])) {
                $token = $_SESSION['users']['token'];
        }
}
if ($users->checkExixst("users", "token", $token) == 1) {
        $data = $users->listOne($token, "token");
        unset($data['password']);
        $_SESSION['users'] = $data;
        $_SESSION['users']['loginTime'] = time();
        $_SESSION['users']['sessionTime'] = time() + 1800;
} else {
        echo "unauthorized";
        return;
}
?>