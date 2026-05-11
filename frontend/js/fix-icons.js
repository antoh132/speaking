/**
 * Fix broken emoji icons caused by UTF-8 encoding issue in dashboard-siswa.html
 */
document.addEventListener('DOMContentLoaded', function () {

  // Fix step headings
  var step1h4 = document.querySelector('#step-1 h4');
  if (step1h4) step1h4.textContent = '🔊 Step 1 – Dengarkan Audio Model';

  var step2h4 = document.querySelector('#step-2 h4');
  if (step2h4) step2h4.textContent = '✏️ Step 2 – Latihan Berbicara Terbimbing';

  var step3h4 = document.querySelector('#step-3 h4');
  if (step3h4) step3h4.textContent = '🎤 Step 3 – Rekam Suaramu';

  var step3prompth4 = document.querySelector('#step-3 .rec-prompt-box h4');
  if (step3prompth4) step3prompth4.textContent = '📝 Prompt Berbicara:';

  var step4h4 = document.querySelector('#step-4 h4');
  if (step4h4) step4h4.textContent = '🎭 Step 4 – Latihan Berbasis Skenario';

  // Fix dialogue box heading
  var dialogueH4 = document.querySelector('.dialogue-box h4');
  if (dialogueH4) dialogueH4.textContent = '💬 Contoh Dialog';

  // Fix feedback section heading
  var feedbackH2 = document.querySelector('#feedback-section h2');
  if (feedbackH2) feedbackH2.textContent = '💬 Feedback dari Dosen';

  // Fix close button
  var closeBtn = document.querySelector('.learn-panel-close');
  if (closeBtn) closeBtn.textContent = '✕ Tutup';

  // Fix play buttons
  var playBtn = document.querySelector('.btn-play');
  if (playBtn) playBtn.textContent = '▶ Putar Audio';

  var playDialogueBtn = document.querySelector('button[onclick="playDialogue()"]');
  if (playDialogueBtn) playDialogueBtn.textContent = '▶ Putar Dialog';

  // Fix step 3 buttons
  var speakPromptBtn = document.querySelector('button[onclick="speakPrompt()"]');
  if (speakPromptBtn) speakPromptBtn.textContent = '🔊 Dengar Contoh';

  var recStart = document.getElementById('btn-rec-start');
  if (recStart) recStart.textContent = '⏺ Mulai Rekam';

  var recStop = document.getElementById('btn-rec-stop');
  if (recStop) recStop.textContent = '⏹ Berhenti';

  var uploadBtn = document.getElementById('btn-upload');
  if (uploadBtn) uploadBtn.textContent = '⬆ Upload Rekaman';

  // Fix step 4 buttons
  var playScenarioBtn = document.querySelector('button[onclick="playScenario()"]');
  if (playScenarioBtn) playScenarioBtn.textContent = '🔊 Baca Skenario';

  var nextScenarioBtn = document.querySelector('button[onclick="nextScenario()"]');
  if (nextScenarioBtn) nextScenarioBtn.textContent = '→ Skenario Berikutnya';

  var sampleAnswerBtn = document.querySelector('button[onclick="toggleSampleAnswer()"]');
  if (sampleAnswerBtn) sampleAnswerBtn.textContent = '💡 Lihat Contoh Jawaban';

  var speakSampleBtn = document.querySelector('button[onclick="speakSample()"]');
  if (speakSampleBtn) speakSampleBtn.textContent = '🔊 Dengar Contoh';

  var scStart = document.getElementById('btn-sc-start');
  if (scStart) scStart.textContent = '⏺ Rekam Respons';

  var scStop = document.getElementById('btn-sc-stop');
  if (scStop) scStop.textContent = '⏹ Berhenti';

  var finishBtn = document.querySelector('button[onclick="finishLevel()"]');
  if (finishBtn) finishBtn.textContent = '✅ Selesai Level Ini';

  // Fix refresh button
  var refreshBtn = document.querySelector('button[onclick="loadFeedback()"]');
  if (refreshBtn) refreshBtn.textContent = '↻ Refresh';

  // Fix step nav arrows
  document.querySelectorAll('.step-nav button').forEach(function (btn) {
    var txt = btn.textContent.trim();
    if (txt.indexOf('Kembali') !== -1) btn.textContent = '← Kembali';
    if (txt.indexOf('Lanjut ke Latihan') !== -1) btn.textContent = 'Lanjut ke Latihan →';
    if (txt.indexOf('Lanjut ke Rekam') !== -1) btn.textContent = 'Lanjut ke Rekam →';
    if (txt.indexOf('Lanjut ke Skenario') !== -1) btn.textContent = 'Lanjut ke Skenario →';
  });

  // Fix progress description
  document.querySelectorAll('.section p').forEach(function (p) {
    if (p.textContent.indexOf('Dengarkan') !== -1 && p.textContent.indexOf('Latihan') !== -1) {
      p.textContent = 'Klik level untuk mulai belajar. Ikuti 4 tahap: Dengarkan → Latihan → Rekam → Skenario';
    }
  });

  // Fix notification bell
  var notifBtn = document.getElementById('notif-btn');
  if (notifBtn) {
    var badge = document.getElementById('notif-badge');
    notifBtn.textContent = '🔔 ';
    if (badge) notifBtn.appendChild(badge);
  }

  // Fix step tab labels
  var tab1span = document.querySelector('#tab-1 span:last-child');
  if (tab1span) tab1span.textContent = ' 🎧 Dengarkan';

  var tab2span = document.querySelector('#tab-2 span:last-child');
  if (tab2span) tab2span.textContent = ' ✏️ Latihan';

  var tab3span = document.querySelector('#tab-3 span:last-child');
  if (tab3span) tab3span.textContent = ' 🎤 Rekam';

  var tab4span = document.querySelector('#tab-4 span:last-child');
  if (tab4span) tab4span.textContent = ' 🎭 Skenario';

  // Fix learn panel title em-dash
  var panelTitle = document.getElementById('learn-panel-title');
  if (panelTitle) {
    panelTitle.textContent = panelTitle.textContent
      .replace(/\s*â€"\s*/g, ' – ')
      .replace(/â€"/g, '–');
  }

  // Fix page title
  document.title = 'SpeakOn! – Dashboard Siswa';
});
