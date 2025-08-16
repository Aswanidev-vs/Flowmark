<?php

require "../config/config.php";
session_start();

header('Content-Type: application/json');

$response = [
    'reply' => '',
    'refresh' => false
];

if (!isset($_SESSION['user_id'])) {
    $response['reply'] = "Please log in to use the chatbot.";
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$message = strtolower(trim($_POST['message'] ?? ''));

$parts = explode(' ', $message);
$command = $parts[0] ?? '';
$task_string = implode(' ', array_slice($parts, 1));

switch ($command) {
    case 'help':
        $response['reply'] = "Available commands:\n" .
                             "• add <taskname> [status <not started|in progress|completed>] [priority <high|medium|low>] [due YYYY-MM-DD]\n" .
                             "• list\n" .
                             "• stats\n" .
                             "• complete <task_id>\n" .
                             "• delete <task_id>\n" .
                             "• clr\n";
        break;

    case 'clr':
        $response['reply'] = "clear_chat_signal";
        break;

    case 'add':
        $taskname = '';
        $status = 'Not Started';
        $priority = 'Medium';
        $due_date = null;

        // Extract keywords and their values
        $words = explode(' ', $task_string);
        $task_words = [];
        $i = 0;
        while ($i < count($words)) {
            $word = $words[$i];
            if ($word === 'status') {
                $status_value = $words[$i+1] ?? '';
                if ($status_value === 'not' && ($words[$i+2] ?? '') === 'started') {
                    $status = 'Not Started';
                    $i += 2;
                } elseif ($status_value === 'in' && ($words[$i+2] ?? '') === 'progress') {
                    $status = 'In Progress';
                    $i += 2;
                } elseif ($status_value === 'completed') {
                    $status = 'Completed';
                    $i += 1;
                }
            } elseif ($word === 'priority') {
                $priority_value = $words[$i+1] ?? '';
                if (in_array($priority_value, ['high', 'medium', 'low'])) {
                    $priority = ucfirst($priority_value);
                    $i += 1;
                }
            } elseif ($word === 'due') {
                $date_value = $words[$i+1] ?? '';
                if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $date_value)) {
                    $due_date = $date_value;
                    $i += 1;
                }
            } else {
                $task_words[] = $word;
            }
            $i++;
        }
        $taskname = implode(' ', $task_words);

        if (empty($taskname)) {
            $response['reply'] = "Please provide a task name.";
        } else {
            $sql = "INSERT INTO task (taskname, user_id, status, due_date, priority) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sisss", $taskname, $user_id, $status, $due_date, $priority);
            if (mysqli_stmt_execute($stmt)) {
                $response['reply'] = "Task '" . htmlspecialchars($taskname) . "' added successfully.\n" .
                                     "Status: " . htmlspecialchars($status) . "\n" .
                                     "Priority: " . htmlspecialchars($priority) . "\n" .
                                     "Due Date: " . ($due_date ? htmlspecialchars($due_date) : "N/A");
                $response['refresh'] = true;
            } else {
                $response['reply'] = "Error adding task: " . mysqli_error($conn);
            }
        }
        break;

    case 'list':
        $sql = "SELECT taskid, taskname, status, due_date, priority FROM task WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $reply = "📋 Your tasks:\n";
            while ($row = mysqli_fetch_assoc($result)) {
                $reply .= "• [" . htmlspecialchars($row['taskid']) . "] " . htmlspecialchars($row['taskname']) . " - " . htmlspecialchars($row['status']);
                if ($row['due_date']) {
                    $reply .= " (Due: " . htmlspecialchars($row['due_date']) . ")";
                }
                $reply .= " (Priority: " . htmlspecialchars($row['priority']) . ")";
                $reply .= "\n";
            }
            $response['reply'] = $reply;
        } else {
            $response['reply'] = "You have no tasks.";
        }
        break;

    case 'complete':
    case 'delete':
        $taskid = $parts[1] ?? '';
        if (empty($taskid) || !is_numeric($taskid)) {
            $response['reply'] = "Please specify a valid task ID.";
        } else {
            if ($command == 'complete') {
                $sql = "UPDATE task SET status = 'Completed', updated_at = NOW() WHERE taskid = ? AND user_id = ?";
                $action = "marked as completed";
            } else { // 'delete'
                $sql = "DELETE FROM task WHERE taskid = ? AND user_id = ?";
                $action = "deleted";
            }
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $taskid, $user_id);
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                $response['reply'] = "Task ID " . htmlspecialchars($taskid) . " has been " . $action . ".";
                $response['refresh'] = true;
            } else {
                $response['reply'] = "Task ID " . htmlspecialchars($taskid) . " not found or you don't have permission to modify it.";
            }
        }
        break;

    case 'stats':
        $sql = "SELECT status, COUNT(*) AS count FROM task WHERE user_id = ? GROUP BY status";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $counts = ['Not Started' => 0, 'In Progress' => 0, 'Completed' => 0];
        while ($row = mysqli_fetch_assoc($result)) {
            $counts[$row['status']] = $row['count'];
        }
        $total = array_sum($counts);
        $response['reply'] = "📊 Your Task Stats:\n" .
                             "Total: {$total}\n" .
                             "Not Started: {$counts['Not Started']}\n" .
                             "In Progress: {$counts['In Progress']}\n" .
                             "Completed: {$counts['Completed']}";
        break;

    default:
        $response['reply'] = "Invalid command. Type 'help' for a list of commands.";
        break;
}

echo json_encode($response);
?>