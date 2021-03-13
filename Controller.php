<?php
require_once './model/Database.php';

require_once './model/Validator.php';

class Controller
{
    private $action;
    private $db;

    public function __construct()
    {
        $this->ensureSecureConnection();
        $this->startSession();
        $this->connectToDatabase();
        $this->action = $this->getAction();

        $this->validator = new Validator();
        $this->validator->addField('username', 'Must be 1-20 characters');
        $this->validator->addField('password', '8 character minimum & contains numbers, lowercase & uppercase letters');
    }

    public function invoke()
    {
        switch ($this->action) {
            case 'Show Login':
                $this->processShowLogin();
                break;
            case 'Login':
                $this->processLogin();
                break;
            case 'Show Registration':
                $this->processShowRegistration();
                break;
            case 'Register':
                $this->processRegister();
                break;
            case 'Logout':
                $this->processLogout();
                break;
            case 'Add Task':
                $this->processAddTask();
                break;
            case 'Delete Task':
                $this->processDeleteTask();
                break;
            case 'Show Tasks':
                $this->processShowTasks();
                break;
            case 'Home':
                $this->processShowHomePage();
                break;
            default:
                $this->processShowHomePage();
                break;
        }
    }

    /****************************************************************
     * Process Request
     ***************************************************************/
    private function processShowLogin()
    {
        $login_message = '';
        include('./view/login.php');
    }

    private function processLogin()
    {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        if ($this->db->isValidUserLogin($username, $password)) {
            $_SESSION['is_valid_user'] = true;
            $_SESSION['username'] = $username;
            header("Location: .?action=Show Tasks");
        } else {
            $login_message = 'Invalid username or password';
            include('./view/login.php');
        }
    }

    /*----------------------------------------*
    * Start User Registration
    *----------------------------------------*/
    private function processShowRegistration()
    {
        $username = '';
        $password = '';
        $registration_message = '';
        $fields = $this->validator->getFields();
        include './view/register.php';
    }

    private function processRegister()
    {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $fields = $this->validator->getFields();
        $registration_message = '';

        
        $fields = $this->validator->getFields();
        $this->validator->checkUsername('username', $username);
        
        $this->validator->checkPassword('password', $password);
        
        $isValid = true;

        if ($this->validator->foundErrors()) {
            $isValid = false;
        }

        if ($this->db->containsUsername($username)) {
            $isValid = false;
            $registration_message = 'Account with that username has already been created';
        }
        
        if (!$isValid) {
            include './view/register.php';
            return;
        }

        $password = password_hash($password, PASSWORD_BCRYPT);

        $this->db->registerUser($username, $password);

        $_SESSION['is_valid_user'] = true;
        $_SESSION['username'] = $username;
        header("Location: .?action=Show Tasks");
    }
    /*----------------------------------------*
    * End User Registration
    *----------------------------------------*/

    private function processShowHomePage()
    {
        include './view/home.php';
    }

    private function processLogout()
    {
        $_SESSION = array();   // Clear all session data from memory
        session_destroy();     // Clean up the session ID
        $login_message = 'You have been logged out.';
        include('./view/login.php');
    }

    private function processShowTasks()
    {
        if (!isset($_SESSION['is_valid_user'])) {
            $login_message = 'Log in to manage your tasks.';
            include('./view/login.php');
        } else {
            $errors = array();
            $tasks = $this->db->getTasksForUser($_SESSION['username']);
            include './view/task_list.php';
        }
    }

    private function processAddTask()
    {
        $new_task = filter_input(INPUT_POST, 'newtask', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $errors = array();
        if (empty($new_task)) {
            $errors[] = 'The new task cannot be empty.';
        } else {
            $this->db->addTask($_SESSION['username'], $new_task);
        }
        $tasks = $this->db->getTasksForUser($_SESSION['username']);
        include './view/task_list.php';
    }

    private function processDeleteTask()
    {
        $task_id = filter_input(INPUT_POST, 'taskid', FILTER_VALIDATE_INT);
        $errors = array();
        if ($task_id === NULL || $task_id === FALSE) {
            $this->errors[] = 'The task cannot be deleted.';
        } else {
            $this->db->deleteTask($task_id);
        }
        $tasks = $this->db->getTasksForUser($_SESSION['username']);
        include './view/task_list.php';
    }

    /****************************************************************
     * Get action from $_GET or $_POST array
     ***************************************************************/
    private function getAction()
    {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($action !== NULL) {
            return $action;
        }

        $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($action !== NULL) {
            return $action;
        }

        return '';
    }

    /****************************************************************
     * Ensure a secure connection and start session
     ***************************************************************/
    private function startSession()
    {
        session_start();
    }

    private function ensureSecureConnection()
    {
        $https = filter_input(INPUT_SERVER, 'HTTPS');

        if (!$https) {
            $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
            $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
            $url = 'https:' . $host . $uri;
            header("Location: $url");
            exit();
        }
    }

    /****************************************************************
     * Connect to the database
     ***************************************************************/
    private function connectToDatabase()
    {
        $this->db = new Database();
        if (!$this->db->isConnected()) {
            $error_message = $this->db->getErrorMessage();
            include './view/database_error.php';
            exit();
        }
    }
}
