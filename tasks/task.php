<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
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
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Add Task with Voice Features</title>
    <link rel="icon"  type="image/png" href="../public/assets/images/checked.png">
 
  <style>
   * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      margin: 0;
      padding: 20px;
      background-color: #212121; /* Updated background */
      color: #e0e0e0;
    }

    form {
      max-width: 500px;
      margin: auto;
      background-color: #2a2a2a; /* Slightly lighter for contrast */
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #ffffff;
    }

    input[type="text"],
    textarea,
    select {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #444;
      border-radius: 10px;
      margin-top: 8px;
      margin-bottom: 16px;
      font-size: 15px;
      background-color: #333;
      color: #f0f0f0;
      transition: 0.3s ease;
    }

    input[type="text"]:focus,
    textarea:focus,
    select:focus {
      border-color: #64b5f6;
      outline: none;
      background-color: #3d3d3d;
      color: #fff;
    }

    textarea {
      resize: vertical;
      min-height: 120px;
    }

    button {
      padding: 10px 20px;
      border: none;
      border-radius: 30px;
      margin: 5px 5px 10px 0;
      background-color: #64b5f6;
      color: #fff;
      cursor: pointer;
      transition: background 0.3s ease;
      font-size: 14px;
      font-weight: bold;
    }

    button:disabled {
      background-color: #555;
      cursor: not-allowed;
    }

    button:hover:not(:disabled) {
      background-color: #42a5f5;
    }

    #speak-text {
      background-color: #9575cd;
    }

    #speak-text:hover {
      background-color: #7e57c2;
    }

    input[type="submit"] {
      background-color: #43a047;
      color: white;
      border: none;
      font-size: 16px;
      font-weight: bold;
      padding: 12px 25px;
      cursor: pointer;
      border-radius: 10px;
      transition: background-color 0.3s ease;
      width: 100%;
    }

    input[type="submit"]:hover {
      background-color: #388e3c;
    }

    label {
      font-weight: 500;
      display: block;
      margin-top: 10px;
    }

    @media (max-width: 600px) {
      form {
        padding: 20px;
      }

      button {
        width: 100%;
        margin-bottom: 10px;
      }

      input[type="submit"] {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>

  <form action="" method="POST">
    <h2>Add Task</h2>
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

  // === Runtime STT Check ===
  async function checkSTTSupport() {
    const isSpeechSupported = 'webkitSpeechRecognition' in window;
    if (!isSpeechSupported) return false;

    // Try to detect Brave accurately
    const isProbablyBrave = navigator.brave && await navigator.brave.isBrave?.();
    const userAgent = navigator.userAgent;
    const isEdge = userAgent.includes("Edg/");

    const isDefinitelySupported = !!window.chrome && !isEdge && !isProbablyBrave;
    return isDefinitelySupported;
  }

  checkSTTSupport().then((supported) => {
    if (supported) {
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

      // Set up event listeners only if STT supported
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

    } else {
      // Disable buttons and show actual alert
      startButton.disabled = true;
      pauseButton.disabled = true;
      stopButton.disabled = true;
      alert("⚠️ Speech-to-Text (STT) is not supported in your browser.\n\nPlease use the latest version of Google Chrome for this feature.");
    }
  });

  // === Text-to-Speech (TTS) Setup ===
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
