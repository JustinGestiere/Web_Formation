<?php
class ErrorHandler {
    public static function handleError($errno, $errstr, $errfile, $errline) {
        error_log("Erreur [$errno] : $errstr dans $errfile à la ligne $errline");
        return true;
    }

    public static function handleException($exception) {
        error_log("Exception : " . $exception->getMessage());
        if (!headers_sent()) {
            header("Location: /error.php");
        }
        exit();
    }

    public static function logError($message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        error_log("[$timestamp] $message $contextStr");
    }
}

// Configurer les gestionnaires d'erreurs
set_error_handler([ErrorHandler::class, 'handleError']);
set_exception_handler([ErrorHandler::class, 'handleException']);
