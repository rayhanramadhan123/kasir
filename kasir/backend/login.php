<?php 
ob_start(); // Start output buffering to prevent headers already sent errors
session_start();
require __DIR__ . '/../database/koneksi.php'; 

$error = "";

// Check for toast message (though this won't be used here since we're redirecting)
if (isset($_SESSION['toast'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', () => {
            showToast('" . htmlspecialchars($_SESSION['toast']['message']) . "', '" . $_SESSION['toast']['type'] . "');
        });
    </script>";
    unset($_SESSION['toast']);
}

// Cek jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan Password harus diisi.";
    } else {
        // Cek di tabel admin terlebih dahulu
        $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        if (!$stmt_admin) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt_admin->bind_param("s", $username);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            if ($result_admin->num_rows > 0) {
                $admin = $result_admin->fetch_assoc();
                $storedPassword = $admin['password'];

                // Cek password untuk admin (plain text comparison)
                if ($password === $storedPassword) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['role'] = 'admin';
                    $_SESSION['admin'] = [
                        'id_admin' => $admin['id_admin'],
                        'username' => $admin['username']
                    ];

                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Set success toast message
                    $_SESSION['toast'] = [
                        'type' => 'success',
                        'message' => 'Login berhasil! '
                    ];

                    // Redirect ke dashboard
                    header("Location: ../dashboard/dashboard.php");
                    exit();
                } else {
                    $error = "Password salah.";
                }
                $stmt_admin->close();
            } else {
                // Jika tidak ditemukan di tabel admin, cek di tabel users
                $stmt_admin->close();
                $stmt_user = $conn->prepare("SELECT * FROM users WHERE username = ?");
                if (!$stmt_user) {
                    $error = "Database error: " . $conn->error;
                } else {
                    $stmt_user->bind_param("s", $username);
                    $stmt_user->execute();
                    $result_user = $stmt_user->get_result();

                    if ($result_user->num_rows > 0) {
                        $user = $result_user->fetch_assoc();
                        $storedPassword = $user['password'];

                        // Cek password untuk user (plain text comparison)
                        if ($password === $storedPassword) {
                            $_SESSION['loggedin'] = true;
                            $_SESSION['role'] = 'user';
                            $_SESSION['user'] = [
                                'id_user' => $user['id_user'],
                                'username' => $user['username']
                            ];

                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);

                            // Set success toast message
                            $_SESSION['toast'] = [
                                'type' => 'success',
                                'message' => 'Login berhasil! '
                            ];

                            // Redirect ke halaman kasir
                            header("Location: ../transaksi/kasir.php");
                            exit();
                        } else {
                            $error = "Password salah.";
                        }
                    } else {
                        $error = "Username tidak ditemukan.";
                    }
                    $stmt_user->close();
                }
            }
        }
    }
}

$conn->close(); // Close database connection
ob_end_flush(); // End output buffering
?>