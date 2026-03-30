<?php

/**
 * This function is used to display flash messages
 *
 * @param string $name The name of the flash message
 * @param string $message The message to be displayed
 * @param string $class The CSS class of the message box
 */
function flashMsg(string $name = '', string $message = '', string $class = 'alert alert-success'): void
{
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            if (!empty($_SESSION[$name])) {
                unset($_SESSION[$name]);
            }

            if (!empty($_SESSION[$name . '_class'])) {
                unset($_SESSION[$name . '_class']);
            }

            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

// ALERT MESSAGE
function alert($type, $message)
{
    $class = ($type == "success") ? "alert-success" : "alert-danger";

    echo <<<alert
    <div aria-live="polite" aria-atomic="true" class="position-relative">
        <div class="toast-container top-0 end-0 p-3">
            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Message</strong>
                    <small class="text-muted">just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body text-dark">
                    $message
                </div>
            </div>
        </div>
    </div>
   alert;
}

/**
 * Check if the user is logged in
 *
 * @return bool true if the user is logged in, false otherwise
 */
function isUserLoggedIn(): bool
{
    if (isset($_SESSION['email'])) {
        return true;
    }

    return false;
}

/**
 * This function is used to redirect the user to a specific page
 *
 * @param string $page The page to be redirected to
 */
function redirect(string $page): void
{
    header("Location: " . URLROOT . "/" . $page);
}

/**
 * This function is used to filter user input data
 *
 * @param array $type The input data to be filtered
 * @return array The filtered input data
 */
function filteration(array $type): array
{
    foreach ($type as $key => $value) {
        // Trim leading and trailing spaces
        $value = trim($value);
        // Remove backslashes
        $value = stripslashes($value);
        // Remove HTML tags
        $value = strip_tags($value);
        // Encode special characters in a string
        $value = htmlspecialchars($value);
        // Assign the filtered value back to the original array
        $type[$key] = $value;
    }
    return $type;
}

/**
 * This function is used to add a logo
 */
function addLogo($logoName, $folder)
{
    if (empty($_FILES[$logoName]['name']) || !is_uploaded_file($_FILES[$logoName]['tmp_name'] ?? '')) {
        alert('message', 'No image uploaded');
        return null;
    }

    $allowed = ['png','jpg','jpeg','svg'];
    $ext = strtolower(pathinfo($_FILES[$logoName]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) {
        alert('message', 'Unsupported image type');
        return null;
    }

    $size = (int)($_FILES[$logoName]['size'] ?? 0);
    if ($size <= 0 || $size > 2 * 1024 * 1024) {
        alert('message', 'Image too large');
        return null;
    }

    $image = 'QRL-' . random_int(11111, 99999) . '.' . $ext;
    $path = 'images/' . trim($folder, '/\\') . '/';
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }

    if (move_uploaded_file($_FILES[$logoName]['tmp_name'], $path . $image)) {
        alert('success', 'Image uploaded successfully');
        return $image;
    }

    alert('message', 'Image Upload Error');
    return null;
}

function imageUpload(string $img_name, string $path)
{
    $ext = pathinfo($_FILES[$img_name]['name'], PATHINFO_EXTENSION);
    $image = random_int(1111111, 9999999) . '.' . $ext;

    if (move_uploaded_file($_FILES[$img_name]['tmp_name'], $path . $image)) {
        return $image;
    } else {
        flashMsg('success', 'Image Upload Error', 'alert-danger');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): void
{
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="' . $t . '">';
}

function csrf_verify($token): bool
{
    if (!is_string($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
