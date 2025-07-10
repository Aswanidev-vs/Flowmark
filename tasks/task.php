<?php
session_start();
require "../config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['submit'])) {
    $taskname = $_POST['taskname'];
    $description =  $_POST['description'];
    $status =  $_POST['status'];

    $sql = "INSERT INTO task (taskname, description, status, user_id) VALUES ('$taskname', '$description', '$status', '$user_id')";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo mysqli_error($conn);
    } else {
        header("Location: Todo.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Add Task with Voice Features</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
    }
    textarea {
      width: 300px;
      height: 200px;
      border-radius: 10px;
    }
    button {
      margin: 5px 10px 5px 0;
      padding: 5px 10px;
      border-radius: 50px;
    }
    input[type="text"]{
      padding: 10px 67px;
      border-radius: 10px;
      margin-top: 10px;
    }
    textarea{
      margin-top: 10px;
      margin-bottom: 10px;
    }
    #status{
      padding: 10px 15px;
      border-radius: 10px;
      margin-top: 10px;
      margin-bottom: 10px;
    }
    input[type="submit"]{
      padding: 5px 20px;
      border-radius: 10px;
    }
    .home-page{
      text-decoration: none;
      padding: 10px 15px;
      border: 2px solid black;
      border-radius: 50px;
    }
  </style>
</head>
<body>

  <h2>Add Task</h2>
  <form action="" method="POST">
    Task Name:<br>
    <input type="text" name="taskname" required><br><br>

    Description:<br>
    <textarea name="description" id="description" placeholder="Enter the contents here......."></textarea><br>

    <!-- Voice Control Buttons -->
    <button type="button" id="start-recording">🎙 Start Recording</button>
    <button type="button" id="pause-recording" disabled>⏸ Pause</button>
    <button type="button" id="stop-recording" disabled>⏹ Stop</button>
    <button type="button" id="speak-text">🔊 Speak Description</button><br><br>
    <label for="status">Task Status:</label><br>
    <select name="status" id="status">
      <option value="Not Started">Not Started</option>
      <option value="In Progress">In Progress</option>
      <option value="Completed">Completed</option>
    </select><br><br>

    <input type="submit" value="OK" name="submit">
  </form>

  <script>
    const startButton = document.getElementById('start-recording');
    const pauseButton = document.getElementById('pause-recording');
    const stopButton = document.getElementById('stop-recording');
    const speakButton = document.getElementById('speak-text');
    const descriptionTextarea = document.getElementById('description');

    let recognition;
    let isPaused = false;
    let isRecording = false;
    let finalTranscript = '';

    if ('webkitSpeechRecognition' in window) {
      recognition = new webkitSpeechRecognition();
      recognition.continuous = true;
      recognition.interimResults = true;

      recognition.onstart = function () {
        isRecording = true;
        startButton.disabled = true;
        pauseButton.disabled = false;
        stopButton.disabled = false;
      };

      recognition.onresult = function (event) {
        let interimTranscript = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
          if (event.results[i].isFinal) {
            finalTranscript += event.results[i][0].transcript;
          } else {
            interimTranscript += event.results[i][0].transcript + ' ';
          }
        }
        descriptionTextarea.value = finalTranscript + interimTranscript;
      };

      recognition.onend = function () {
        isRecording = false;
        startButton.disabled = false;
        stopButton.disabled = true;

        if (isPaused) {
          pauseButton.textContent = '▶ Resume';
          pauseButton.disabled = false;
        } else {
          pauseButton.disabled = true;
          pauseButton.textContent = '⏸ Pause';
        }
      };
    } else {
      alert('Speech Recognition is not supported in this browser.');
    }

    startButton.addEventListener('click', () => {
      if (recognition && !isRecording) {
        isPaused = false;
        finalTranscript = descriptionTextarea.value;
        recognition.start();
      }
    });

    pauseButton.addEventListener('click', () => {
      if (!recognition) return;

      if (!isPaused) {
        isPaused = true;
        recognition.stop();
        pauseButton.textContent = '▶ Resume';
      } else {
        isPaused = false;
        finalTranscript = descriptionTextarea.value;
        recognition.start();
        pauseButton.textContent = '⏸ Pause';
      }
    });

    stopButton.addEventListener('click', () => {
      if (recognition) {
        isPaused = false;
        recognition.stop();
        pauseButton.textContent = '⏸ Pause';
      }
    });

    let selectedVoice = null;

    function loadVoices() {
      const voices = window.speechSynthesis.getVoices();

      selectedVoice = voices.find(voice =>
        voice.name.includes('Google') && voice.name.toLowerCase().includes('female')
      ) || voices.find(voice =>
        voice.name.toLowerCase().includes('zira')
      ) || voices.find(voice =>
        voice.lang === 'en-US'
      );

      if (!selectedVoice && voices.length > 0) {
        selectedVoice = voices[0];
      }
    }

    window.speechSynthesis.onvoiceschanged = loadVoices;
    loadVoices();

    speakButton.addEventListener('click', () => {
      const text = descriptionTextarea.value.trim();
      if (!text) {
        alert("No description text to speak!");
        return;
      }

      const utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'en-US';
      utterance.rate = 1;
      utterance.pitch = 1.2;

      if (selectedVoice) {
        utterance.voice = selectedVoice;
      }

      window.speechSynthesis.speak(utterance);
    });
  </script>

</body>
</html>
