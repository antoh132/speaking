/**
 * Fix broken emoji icons caused by UTF-8 encoding issue in dashboard-siswa.html
 * This script replaces broken mojibake text with correct emoji using innerHTML replacement
 * on specific known elements.
 */
document.addEventListener('DOMContentLoaded', function () {

  // Elements with known broken text — replace innerHTML directly
  var elementFixes = [
    // Step headings
    { sel: '#step-1 h4',        html: '&#128266; Step 1 &ndash; Dengarkan Audio Model' },
    { sel: '#step-2 h4',        html: '&#9999;&#65039; Step 2 &ndash; Latihan Berbicara Terbimbing' },
    { sel: '#step-3 h4',        html: '&#127908; Step 3 &ndash; Rekam Suaramu' },
    { sel: '#step-3 .rec-prompt-box h4', html: '&#128221; Prompt Berbicara:' },
    { sel: '#step-4 h4',        html: '&#127917; Step 4 &ndash; Latihan Berbasis Skenario' },
    // Feedback section heading
    { sel: '#feedback-section h2', html: '&#128172; Feedback dari Dosen' },
    // Dialogue box heading
    { sel: '.dialogue-box h4',  html: '&#128172; Contoh Dialog' },
  ];

  elementFixes.forEach(function (fix) {
    var el = document.querySelector(fix.sel);
    if (el) el.innerHTML = fix.html;
  });

  // Fix button text nodes — use textContent for buttons
  var buttonFixes = [
    // Nav notification button (keep badge inside)
    { sel: '#notif-btn',         prefix: '&#128276; ' },
    // Play buttons
    { sel: '.btn-play',          text: '&#9654; Putar Audio' },
    // Close panel button
    { sel: '.learn-panel-close', text: '&#10005; Tutup' },
    // Upload button
    { sel: '#btn-upload',        text: '&#11014; Upload Rekaman' },
    // Finish level button
    { sel: 'button[onclick="finishLevel()"]', text: '&#10003; Selesai Level Ini' },
    // Refresh feedback button
    { sel: 'button[onclick="loadFeedback()"]', text: '&#8635; Refresh' },
  ];

  buttonFixes.forEach(function (fix) {
    var el = document.querySelector(fix.sel);
    if (!el) return;
    if (fix.text) {
      el.textContent = fix.text;
    }
  });

  // Fix buttons with dynamic text using querySelectorAll
  // "Putar Dialog" button
  var playDialogueBtn = document.querySelector('button[onclick="playDialogue()"]');
  if (playDialogueBtn) playDialogueBtn.innerHTML = '&#9654; Putar Dialog';

  // "Dengar Contoh" buttons in step 3
  var speakPromptBtn = document.querySelector('button[onclick="speakPrompt()"]');
  if (speakPromptBtn) speakPromptBtn.innerHTML = '&#128266; Dengar Contoh';

  // Record start/stop buttons
  var recStart = document.getElementById('btn-rec-start');
  if (recStart) recStart.innerHTML = '&#9210; Mulai Rekam';

  var recStop = document.getElementById('btn-rec-stop');
  if (recStop) recStop.innerHTML = '&#9209; Berhenti';

  var scStart = document.getElementById('btn-sc-start');
  if (scStart) scStart.innerHTML = '&#9210; Rekam Respons';

  var scStop = document.getElementById('btn-sc-stop');
  if (scStop) scStop.innerHTML = '&#9209; Berhenti';

  // Scenario buttons
  var playScenarioBtn = document.querySelector('button[onclick="playScenario()"]');
  if (playScenarioBtn) playScenarioBtn.innerHTML = '&#128266; Baca Skenario';

  var nextScenarioBtn = document.querySelector('button[onclick="nextScenario()"]');
  if (nextScenarioBtn) nextScenarioBtn.innerHTML = '&#8594; Skenario Berikutnya';

  var sampleAnswerBtn = document.querySelector('button[onclick="toggleSampleAnswer()"]');
  if (sampleAnswerBtn) sampleAnswerBtn.innerHTML = '&#128161; Lihat Contoh Jawaban';

  var speakSampleBtn = document.querySelector('button[onclick="speakSample()"]');
  if (speakSampleBtn) speakSampleBtn.innerHTML = '&#128266; Dengar Contoh';

  // Navigation arrows — fix all step-nav buttons
  document.querySelectorAll('.step-nav button').forEach(function (btn) {
    var txt = btn.textContent;
    // "Kembali" buttons
    if (txt.indexOf('Kembali') !== -1) {
      btn.innerHTML = '&#8592; Kembali';
    }
    // "Lanjut ke Latihan" button
    if (txt.indexOf('Lanjut ke Latihan') !== -1) {
      btn.innerHTML = 'Lanjut ke Latihan &#8594;';
    }
    // "Lanjut ke Rekam" button
    if (txt.indexOf('Lanjut ke Rekam') !== -1) {
      btn.innerHTML = 'Lanjut ke Rekam &#8594;';
    }
    // "Lanjut ke Skenario" button
    if (txt.indexOf('Lanjut ke Skenario') !== -1) {
      btn.innerHTML = 'Lanjut ke Skenario &#8594;';
    }
  });

  // Fix progress description text
  var progressDesc = document.querySelector('.section p');
  if (progressDesc && progressDesc.textContent.indexOf('Dengarkan') !== -1) {
    progressDesc.textContent = 'Klik level untuk mulai belajar. Ikuti 4 tahap: Dengarkan \u2192 Latihan \u2192 Rekam \u2192 Skenario';
  }

  // Fix learn panel title default text
  var panelTitle = document.getElementById('learn-panel-title');
  if (panelTitle) {
    panelTitle.textContent = panelTitle.textContent.replace(/\s*â€"\s*/g, ' \u2013 ').replace(/â€"/g, '\u2013');
  }

  // Fix title tag
  document.title = 'SpeakOn! \u2013 Dashboard Siswa';
});
