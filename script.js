// ══════════════════════════════════════════════════════════════
//  SpeakOn! — Main Script
// ══════════════════════════════════════════════════════════════

// ── Data ──────────────────────────────────────────────────────
const topics = [
  {
    id: 'online-learning',
    title: 'Online Learning',
    emoji: '💻',
    desc: 'Share your thoughts about digital education.',
    model: 'I think online learning is effective because it is flexible and accessible anytime.',
    dialogue: [
      { speaker: 'A', text: 'What do you think about online learning?' },
      { speaker: 'B', text: 'I think it is effective because it is flexible and we can study from home.' },
      { speaker: 'A', text: 'Do you prefer it over classroom learning?' },
      { speaker: 'B', text: 'Sometimes, but I also miss interacting with friends in class.' },
    ],
    guidedTasks: [
      {
        level: 'Level 1 — Complete the Sentence',
        instruction: 'Read the sentence below and fill in the blanks. Speak the FULL sentence out loud.',
        template: 'I think online learning is ___ because ___.',
        example: 'I think online learning is effective because it is flexible.',
        keywords: ['online learning', 'because'],
        minWords: 7,
      },
      {
        level: 'Level 2 — Expand Your Answer',
        instruction: 'Read and speak the full sentence. Add your own ideas for the blanks.',
        template: 'I think online learning is ___ because ___. However, ___.',
        example: 'I think online learning is useful because it saves time. However, it can be lonely without friends.',
        keywords: ['online learning', 'because', 'however'],
        minWords: 12,
      },
      {
        level: 'Level 3 — Free Response',
        instruction: 'Answer the question freely in at least 2 sentences. Speak clearly and confidently!',
        template: 'What do you think about online learning? Give your full opinion.',
        example: 'I think online learning is very helpful because we can study anytime. However, I prefer classroom learning because I can interact with my friends.',
        keywords: ['online learning', 'think', 'because'],
        minWords: 15,
      },
    ],
    recordPrompt: 'What do you think about online learning? Share your opinion in 2–3 sentences.',
  },
  {
    id: 'school-rules',
    title: 'School Rules',
    emoji: '📋',
    desc: 'Talk about rules and discipline at school.',
    model: 'I think school rules are important because they help students stay disciplined and focused.',
    dialogue: [
      { speaker: 'A', text: 'What do you think about school rules?' },
      { speaker: 'B', text: 'I think they are important because they make students more disciplined.' },
      { speaker: 'A', text: 'Are there any rules you disagree with?' },
      { speaker: 'B', text: 'Well, I think some rules could be more flexible for students.' },
    ],
    guidedTasks: [
      {
        level: 'Level 1 — Complete the Sentence',
        instruction: 'Read the sentence below and fill in the blanks. Speak the FULL sentence out loud.',
        template: 'I think school rules are ___ because ___.',
        example: 'I think school rules are important because they make students disciplined.',
        keywords: ['school rules', 'because'],
        minWords: 7,
      },
      {
        level: 'Level 2 — Expand Your Answer',
        instruction: 'Read and speak the full sentence. Add your own ideas for the blanks.',
        template: 'I think school rules are ___ because ___. One example is ___.',
        example: 'I think school rules are necessary because they create order. One example is the uniform rule which makes everyone equal.',
        keywords: ['school rules', 'because', 'example'],
        minWords: 12,
      },
      {
        level: 'Level 3 — Free Response',
        instruction: 'Answer the question freely in at least 2 sentences. Speak clearly and confidently!',
        template: 'What do you think about school rules? Do you agree or disagree?',
        example: 'I think school rules are very important because they help maintain order. I agree with most rules, but I think some rules should be more flexible.',
        keywords: ['school rules', 'think', 'because'],
        minWords: 15,
      },
    ],
    recordPrompt: 'What do you think about school rules? Are they important? Give your opinion.',
  },
  {
    id: 'social-media',
    title: 'Social Media',
    emoji: '📱',
    desc: 'Discuss the impact of social media on students.',
    model: 'Social media can be useful for learning, but it can also be a distraction if not used wisely.',
    dialogue: [
      { speaker: 'A', text: 'Do you think social media is good for students?' },
      { speaker: 'B', text: 'It depends. It can help us find information, but it can also waste our time.' },
      { speaker: 'A', text: 'How do you manage your social media use?' },
      { speaker: 'B', text: 'I try to limit my screen time and only use it after finishing my homework.' },
    ],
    guidedTasks: [
      {
        level: 'Level 1 — Complete the Sentence',
        instruction: 'Read the sentence below and fill in the blanks. Speak the FULL sentence out loud.',
        template: 'I think social media is ___ for students because ___.',
        example: 'I think social media is useful for students because it helps us find information.',
        keywords: ['social media', 'because'],
        minWords: 7,
      },
      {
        level: 'Level 2 — Expand Your Answer',
        instruction: 'Read and speak the full sentence. Add your own ideas for the blanks.',
        template: 'Social media can be ___, but it can also ___. Therefore, ___.',
        example: 'Social media can be helpful, but it can also be a distraction. Therefore, we should use it wisely.',
        keywords: ['social media', 'but', 'therefore'],
        minWords: 12,
      },
      {
        level: 'Level 3 — Free Response',
        instruction: 'Answer the question freely in at least 2 sentences. Speak clearly and confidently!',
        template: 'What is your opinion about social media? How does it affect students?',
        example: 'I think social media has both positive and negative effects on students. It can help us learn new things, but it can also make us waste time if we are not careful.',
        keywords: ['social media', 'students', 'think'],
        minWords: 15,
      },
    ],
    recordPrompt: 'Do you think social media is good or bad for students? Explain your reasons.',
  },
  {
    id: 'environment',
    title: 'Environment',
    emoji: '🌿',
    desc: 'Express your views on environmental issues.',
    model: 'We should take care of the environment by reducing waste and saving energy every day.',
    dialogue: [
      { speaker: 'A', text: 'What can students do to help the environment?' },
      { speaker: 'B', text: 'We can start by reducing plastic use and recycling our waste.' },
      { speaker: 'A', text: 'Do you think small actions really make a difference?' },
      { speaker: 'B', text: 'Yes, I believe every small action counts when everyone does it together.' },
    ],
    guidedTasks: [
      {
        level: 'Level 1 — Complete the Sentence',
        instruction: 'Read the sentence below and fill in the blanks. Speak the FULL sentence out loud.',
        template: 'I think we should protect the environment by ___.',
        example: 'I think we should protect the environment by reducing plastic waste.',
        keywords: ['environment', 'protect', 'by'],
        minWords: 7,
      },
      {
        level: 'Level 2 — Expand Your Answer',
        instruction: 'Read and speak the full sentence. Add your own ideas for the blanks.',
        template: 'We can help the environment by ___. This is important because ___.',
        example: 'We can help the environment by recycling and saving water. This is important because the earth needs our care.',
        keywords: ['environment', 'because', 'important'],
        minWords: 12,
      },
      {
        level: 'Level 3 — Free Response',
        instruction: 'Answer the question freely in at least 2 sentences. Speak clearly and confidently!',
        template: 'What do you think about environmental problems? What should students do?',
        example: 'I think environmental problems are very serious and we must act now. Students can help by reducing waste, saving energy, and planting trees in their community.',
        keywords: ['environment', 'students', 'think'],
        minWords: 15,
      },
    ],
    recordPrompt: 'What can you do to help protect the environment? Give at least two ideas.',
  },
  {
    id: 'future-career',
    title: 'Future Career',
    emoji: '🎯',
    desc: 'Talk about your dreams and career goals.',
    model: 'In the future, I want to become a teacher because I enjoy helping others learn new things.',
    dialogue: [
      { speaker: 'A', text: 'What do you want to be in the future?' },
      { speaker: 'B', text: 'I want to be a doctor because I want to help sick people.' },
      { speaker: 'A', text: 'That sounds great! What are you doing to achieve that goal?' },
      { speaker: 'B', text: 'I am studying hard, especially in science subjects.' },
    ],
    guidedTasks: [
      {
        level: 'Level 1 — Complete the Sentence',
        instruction: 'Read the sentence below and fill in the blanks. Speak the FULL sentence out loud.',
        template: 'In the future, I want to be a ___ because ___.',
        example: 'In the future, I want to be a doctor because I want to help people.',
        keywords: ['future', 'want', 'because'],
        minWords: 8,
      },
      {
        level: 'Level 2 — Expand Your Answer',
        instruction: 'Read and speak the full sentence. Add your own ideas for the blanks.',
        template: 'I want to be a ___ because ___. To achieve this, I will ___.',
        example: 'I want to be a teacher because I love helping others. To achieve this, I will study hard and practice every day.',
        keywords: ['want', 'because', 'achieve'],
        minWords: 12,
      },
      {
        level: 'Level 3 — Free Response',
        instruction: 'Answer the question freely in at least 2 sentences. Speak clearly and confidently!',
        template: 'What is your dream career? Why do you want it and how will you achieve it?',
        example: 'My dream career is to become an engineer because I love solving problems. I will achieve this by studying mathematics and science seriously at school.',
        keywords: ['career', 'because', 'want'],
        minWords: 15,
      },
    ],
    recordPrompt: 'What is your dream job? Why do you want it? What are you doing to achieve it?',
  },
  {
    id: 'health',
    title: 'Health & Lifestyle',
    emoji: '🏃',
    desc: 'Discuss healthy habits and lifestyle choices.',
    model: 'Maintaining a healthy lifestyle is important because it helps us stay focused and energetic at school.',
    dialogue: [
      { speaker: 'A', text: 'How do you keep yourself healthy?' },
      { speaker: 'B', text: 'I exercise regularly and try to eat nutritious food every day.' },
      { speaker: 'A', text: 'Do you think students today have a healthy lifestyle?' },
      { speaker: 'B', text: 'Not always. Many students stay up late and skip breakfast, which is not healthy.' },
    ],
    guidedTasks: [
      {
        level: 'Level 1 — Complete the Sentence',
        instruction: 'Read the sentence below and fill in the blanks. Speak the FULL sentence out loud.',
        template: 'To stay healthy, I usually ___.',
        example: 'To stay healthy, I usually exercise every morning and eat vegetables.',
        keywords: ['healthy', 'usually'],
        minWords: 6,
      },
      {
        level: 'Level 2 — Expand Your Answer',
        instruction: 'Read and speak the full sentence. Add your own ideas for the blanks.',
        template: 'I think a healthy lifestyle is important because ___. One thing I do is ___.',
        example: 'I think a healthy lifestyle is important because it keeps our body strong. One thing I do is drink enough water every day.',
        keywords: ['healthy', 'because', 'important'],
        minWords: 12,
      },
      {
        level: 'Level 3 — Free Response',
        instruction: 'Answer the question freely in at least 2 sentences. Speak clearly and confidently!',
        template: 'What does a healthy lifestyle mean to you? How do you practice it?',
        example: 'A healthy lifestyle means taking care of both my body and mind. I practice it by exercising regularly, eating nutritious food, and getting enough sleep every night.',
        keywords: ['healthy', 'lifestyle', 'because'],
        minWords: 15,
      },
    ],
    recordPrompt: 'How do you maintain a healthy lifestyle? What habits do you think are most important?',
  },
];

const scenarios = [
  {
    tag: 'Daily Life',
    text: 'Your friend is always tired because of too much homework. What advice would you give them?',
    hint: 'Think about time management, rest, and asking for help.',
    sample: 'You should try to manage your time better. Make a schedule and take short breaks between studying. Also, make sure you get enough sleep every night.',
  },
  {
    tag: 'School',
    text: 'Your school wants to ban mobile phones during class. What do you think about this rule?',
    hint: 'Consider both the advantages and disadvantages.',
    sample: 'I think banning mobile phones during class is a good idea because it helps students focus. However, phones can also be useful for looking up information, so maybe students should be allowed to use them only for educational purposes.',
  },
  {
    tag: 'Environment',
    text: 'You see your classmate throwing trash on the school floor. What would you say to them?',
    hint: 'Be polite but firm. Explain why it matters.',
    sample: 'Excuse me, could you please pick that up? Keeping our school clean is everyone\'s responsibility. If we all throw trash properly, our school will be a much nicer place for everyone.',
  },
  {
    tag: 'Technology',
    text: 'Your younger sibling spends too much time playing games on their phone. How would you advise them?',
    hint: 'Balance between fun and responsibility.',
    sample: 'I understand that games are fun, but you should limit your screen time. Try to finish your homework first before playing. You can set a timer so you don\'t play for too long.',
  },
  {
    tag: 'Social',
    text: 'A new student joins your class and looks nervous. What would you do or say to make them feel welcome?',
    hint: 'Think about how you would want to be treated in a new place.',
    sample: 'Hi! Welcome to our class. My name is ___. Don\'t worry, everyone here is friendly. If you need any help or have questions about the school, feel free to ask me anytime.',
  },
  {
    tag: 'Opinion',
    text: 'Your teacher asks: "Do you think English is important for your future?" How do you respond?',
    hint: 'Think about jobs, travel, and global communication.',
    sample: 'Yes, I think English is very important for my future. Many job opportunities require English skills, and it also helps me communicate with people from other countries. Learning English opens many doors for my career.',
  },
  {
    tag: 'Problem Solving',
    text: 'You forgot to bring your homework to school. What would you say to your teacher?',
    hint: 'Be honest and polite. Offer a solution.',
    sample: 'I\'m sorry, Miss. I forgot to bring my homework today. I finished it at home, but I left it on my desk. May I bring it tomorrow? I promise it won\'t happen again.',
  },
  {
    tag: 'Daily Life',
    text: 'Your parents want you to study a subject you don\'t like. How would you talk to them about it?',
    hint: 'Express your feelings respectfully and suggest alternatives.',
    sample: 'Mom, Dad, I understand you want the best for me. But I find it really difficult to enjoy this subject. I am more interested in ___ and I think I can do better in that area. Can we talk about what I really want to study?',
  },
];

// ── State ─────────────────────────────────────────────────────
let currentTopic = null;
let currentSpeed = 0.9;
let currentScenarioIndex = 0;
let mediaRecorder = null;
let audioChunks = [];
let recordedBlobs = [];
let isRecording = false;
let isScenarioRecording = false;
let scenarioRecorder = null;
let scenarioChunks = [];
let speechRecognition = null;
let scenarioRecognition = null;

// ── Navigation ────────────────────────────────────────────────
function showPage(pageId) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.bottom-nav-btn').forEach(b => b.classList.remove('active'));

  document.getElementById('page-' + pageId).classList.add('active');

  const navMap = { home: 0, topics: 1, practice: 2, record: 3, scenario: 4 };
  const idx = navMap[pageId];
  document.querySelectorAll('.nav-btn')[idx].classList.add('active');
  document.querySelectorAll('.bottom-nav-btn')[idx].classList.add('active');

  window.scrollTo(0, 0);
}

// ── Topics Page ───────────────────────────────────────────────
function renderTopics() {
  const grid = document.getElementById('topics-grid');
  grid.innerHTML = topics.map(t => `
    <div class="topic-card" onclick="selectTopic('${t.id}')">
      <div class="topic-emoji">${t.emoji}</div>
      <h3>${t.title}</h3>
      <p>${t.desc}</p>
      <button class="btn-topic-start">Start →</button>
    </div>
  `).join('');
}

function selectTopic(id) {
  currentTopic = topics.find(t => t.id === id);
  loadPractice();
  showPage('practice');
}

// ── Practice Page ─────────────────────────────────────────────
function loadPractice() {
  if (!currentTopic) return;

  document.getElementById('practice-topic-label').textContent = `Topic: ${currentTopic.emoji} ${currentTopic.title}`;

  // Audio model
  const modelEl = document.getElementById('model-sentence');
  modelEl.innerHTML = currentTopic.model
    .split(' ')
    .map((w, i) => `<span class="word" id="pw-${i}">${w}</span>`)
    .join(' ');

  // Dialogue
  const dialogueEl = document.getElementById('dialogue-content');
  dialogueEl.innerHTML = currentTopic.dialogue.map(line => `
    <div class="dialogue-line ${line.speaker === 'A' ? 'line-a' : 'line-b'}">
      <span class="speaker-badge">${line.speaker}</span>
      <span class="dialogue-text">${line.text}</span>
    </div>
  `).join('');

  // Guided tasks — with feedback system
  const tasksEl = document.getElementById('guided-tasks-container');
  tasksEl.innerHTML = currentTopic.guidedTasks.map((task, i) => `
    <div class="guided-task" id="guided-task-${i}">
      <div class="task-level">${task.level}</div>
      <div class="task-instruction">📌 ${task.instruction}</div>

      <div class="task-template-box">
        <div class="task-template">${task.template}</div>
        <button class="btn-play-small" onclick="speakText('${task.example.replace(/'/g, "\\'")}')">🔊 Hear Example</button>
      </div>

      <div class="task-example-box" id="example-box-${i}" style="display:none">
        <span class="example-label">💡 Example answer:</span>
        <span class="example-text">${task.example}</span>
      </div>

      <div class="task-actions">
        <button class="btn-record-task" id="btn-task-rec-${i}" onclick="toggleTaskRecording(${i}, this)">🎤 Start Speaking</button>
        <button class="btn-hint-task" onclick="toggleExample(${i})">💡 Show Example</button>
      </div>

      <div class="task-transcript-wrap" id="transcript-wrap-${i}" style="display:none">
        <div class="task-transcript-label">You said:</div>
        <div class="task-transcript" id="task-transcript-${i}"></div>
        <div class="task-feedback" id="task-feedback-${i}"></div>
      </div>
    </div>
  `).join('');

  // Record page prompt
  document.getElementById('record-prompt').textContent = currentTopic.recordPrompt;
}

function toggleExample(i) {
  const box = document.getElementById(`example-box-${i}`);
  const btn = box.previousElementSibling.querySelector('.btn-hint-task');
  if (box.style.display === 'none') {
    box.style.display = 'block';
    if (btn) btn.textContent = '🙈 Hide Example';
  } else {
    box.style.display = 'none';
    if (btn) btn.textContent = '💡 Show Example';
  }
}

// ── Speed Control ─────────────────────────────────────────────
function setSpeed(speed, btn) {
  currentSpeed = speed;
  document.querySelectorAll('.speed-btn').forEach(b => b.classList.remove('active-speed'));
  btn.classList.add('active-speed');
  // replay if something is being said
  window.speechSynthesis.cancel();
}

// ── Text-to-Speech with word highlight ───────────────────────
function speakText(text, wordPrefix) {
  window.speechSynthesis.cancel();

  if (wordPrefix) clearWordHighlight(wordPrefix);

  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = 'en-US';
  utterance.rate = currentSpeed;
  utterance.pitch = 1;
  utterance.volume = 1;

  if (wordPrefix) {
    let wordIndex = 0;
    utterance.onboundary = (e) => {
      if (e.name === 'word') {
        highlightWord(wordPrefix, wordIndex);
        wordIndex++;
      }
    };
    utterance.onend = () => clearWordHighlight(wordPrefix);
  }

  window.speechSynthesis.speak(utterance);
}

function playModel() {
  if (!currentTopic) return;
  speakText(currentTopic.model, 'pw');
}

function playDialogue() {
  if (!currentTopic) return;
  const fullText = currentTopic.dialogue.map(l => `${l.speaker} says: ${l.text}`).join('. ');
  speakText(fullText);
}

function highlightWord(prefix, index) {
  document.querySelectorAll(`[id^="${prefix}-"]`).forEach((el, i) => {
    el.classList.toggle('word-highlight', i === index);
  });
}

function clearWordHighlight(prefix) {
  document.querySelectorAll(`[id^="${prefix}-"]`).forEach(el => el.classList.remove('word-highlight'));
}

// ── Guided Task Recording + Feedback ─────────────────────────
let taskRecognitions = {};

function toggleTaskRecording(taskIndex, btn) {
  if (taskRecognitions[taskIndex]) {
    taskRecognitions[taskIndex].stop();
    return;
  }

  if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
    alert('Speech recognition is not supported in your browser. Please use Chrome.');
    return;
  }

  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  const rec = new SR();
  rec.lang = 'en-US';
  rec.continuous = false;
  rec.interimResults = true;

  const transcriptWrap = document.getElementById(`transcript-wrap-${taskIndex}`);
  const transcriptEl   = document.getElementById(`task-transcript-${taskIndex}`);
  const feedbackEl     = document.getElementById(`task-feedback-${taskIndex}`);

  transcriptWrap.style.display = 'block';
  transcriptEl.innerHTML = '<span class="listening-pulse">🎙️ Listening...</span>';
  feedbackEl.innerHTML = '';

  let finalTranscript = '';

  rec.onresult = (event) => {
    let interim = '';
    finalTranscript = '';
    for (const res of event.results) {
      if (res.isFinal) {
        finalTranscript += res[0].transcript + ' ';
      } else {
        interim += res[0].transcript;
      }
    }
    transcriptEl.textContent = (finalTranscript || interim).trim();
    transcriptEl.style.color = finalTranscript ? '#222' : '#888';
  };

  rec.onerror = () => {
    transcriptEl.textContent = 'Could not hear you. Please try again.';
    feedbackEl.innerHTML = '';
    btn.textContent = '🎤 Start Speaking';
    btn.classList.remove('recording-active');
    delete taskRecognitions[taskIndex];
  };

  rec.onend = () => {
    btn.textContent = '🎤 Try Again';
    btn.classList.remove('recording-active');
    delete taskRecognitions[taskIndex];

    const spoken = (finalTranscript || transcriptEl.textContent).trim().toLowerCase();
    if (spoken.length > 2) {
      checkAnswer(taskIndex, spoken, feedbackEl);
    }
  };

  rec.start();
  taskRecognitions[taskIndex] = rec;
  btn.textContent = '⏹ Stop';
  btn.classList.add('recording-active');
}

// ── Answer Checking Logic ─────────────────────────────────────
function checkAnswer(taskIndex, spoken, feedbackEl) {
  const task = currentTopic.guidedTasks[taskIndex];
  const wordCount = spoken.trim().split(/\s+/).length;

  // Check required keywords
  const foundKeywords = task.keywords.filter(kw => spoken.includes(kw.toLowerCase()));
  const missingKeywords = task.keywords.filter(kw => !spoken.includes(kw.toLowerCase()));
  const keywordScore = foundKeywords.length / task.keywords.length;

  // Check minimum word count
  const lengthOk = wordCount >= task.minWords;

  // Score: 0–100
  const score = Math.round(keywordScore * 70 + (lengthOk ? 30 : (wordCount / task.minWords) * 30));

  let html = '';

  if (score >= 80) {
    // ✅ Great
    html = `
      <div class="feedback-box feedback-great">
        <div class="feedback-icon">✅</div>
        <div class="feedback-content">
          <div class="feedback-title">Great job! Well done!</div>
          <div class="feedback-detail">You used the key words correctly and spoke enough sentences.</div>
          <div class="feedback-score">Score: ${score}/100</div>
        </div>
      </div>`;
  } else if (score >= 50) {
    // 🟡 Almost
    const tips = [];
    if (missingKeywords.length > 0) tips.push(`Try to include: <strong>${missingKeywords.join(', ')}</strong>`);
    if (!lengthOk) tips.push(`Speak more — try at least ${task.minWords} words (you said ~${wordCount})`);
    html = `
      <div class="feedback-box feedback-almost">
        <div class="feedback-icon">🟡</div>
        <div class="feedback-content">
          <div class="feedback-title">Almost there! Keep trying.</div>
          <div class="feedback-detail">${tips.join('<br>')}</div>
          <div class="feedback-score">Score: ${score}/100</div>
        </div>
      </div>`;
  } else {
    // ❌ Try again
    html = `
      <div class="feedback-box feedback-retry">
        <div class="feedback-icon">❌</div>
        <div class="feedback-content">
          <div class="feedback-title">Let's try again!</div>
          <div class="feedback-detail">
            Make sure to speak the full sentence and include these words:<br>
            <strong>${task.keywords.join(', ')}</strong>
          </div>
          <div class="feedback-score">Score: ${score}/100</div>
          <div class="feedback-example">💡 Example: <em>${task.example}</em></div>
        </div>
      </div>`;
  }

  feedbackEl.innerHTML = html;
}

// ── Record Page ───────────────────────────────────────────────
function toggleRecording() {
  if (isRecording) {
    stopMainRecording();
  } else {
    startMainRecording();
  }
}

function startMainRecording() {
  if (!navigator.mediaDevices) {
    alert('Microphone access is not supported in your browser.');
    return;
  }

  navigator.mediaDevices.getUserMedia({ audio: true }).then(stream => {
    audioChunks = [];
    mediaRecorder = new MediaRecorder(stream);
    mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
    mediaRecorder.onstop = () => {
      const blob = new Blob(audioChunks, { type: 'audio/webm' });
      recordedBlobs.push(blob);
      renderRecordings();
      stream.getTracks().forEach(t => t.stop());
    };
    mediaRecorder.start();
    isRecording = true;

    const btn = document.getElementById('btn-record');
    btn.textContent = '⏹ Stop Recording';
    btn.classList.add('recording-active');

    // Also start live transcript
    startLiveTranscript();
  }).catch(() => {
    alert('Could not access microphone. Please allow microphone permission.');
  });
}

function stopMainRecording() {
  if (mediaRecorder) mediaRecorder.stop();
  if (speechRecognition) speechRecognition.stop();
  isRecording = false;

  const btn = document.getElementById('btn-record');
  btn.textContent = '🎤 Start Recording';
  btn.classList.remove('recording-active');
  document.getElementById('btn-playback').disabled = false;
}

function startLiveTranscript() {
  if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) return;

  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  speechRecognition = new SR();
  speechRecognition.lang = 'en-US';
  speechRecognition.continuous = true;
  speechRecognition.interimResults = true;

  const el = document.getElementById('live-transcript');

  speechRecognition.onresult = (event) => {
    let text = '';
    for (const res of event.results) {
      text = res[0].transcript;
      el.style.color = res.isFinal ? '#222' : '#888';
    }
    el.textContent = text;
  };

  speechRecognition.start();
}

function playRecording() {
  if (recordedBlobs.length === 0) return;
  const blob = recordedBlobs[recordedBlobs.length - 1];
  const url = URL.createObjectURL(blob);
  const audio = new Audio(url);
  audio.play();
}

function clearRecording() {
  recordedBlobs = [];
  audioChunks = [];
  document.getElementById('live-transcript').textContent = 'Press "Start Recording" and speak...';
  document.getElementById('live-transcript').style.color = '';
  document.getElementById('btn-playback').disabled = true;
  renderRecordings();
}

function renderRecordings() {
  const list = document.getElementById('recordings-list');
  const msg = document.getElementById('no-recordings-msg');

  if (recordedBlobs.length === 0) {
    msg.style.display = 'block';
    // remove old audio elements
    list.querySelectorAll('.recording-item').forEach(el => el.remove());
    return;
  }

  msg.style.display = 'none';
  list.querySelectorAll('.recording-item').forEach(el => el.remove());

  recordedBlobs.forEach((blob, i) => {
    const url = URL.createObjectURL(blob);
    const div = document.createElement('div');
    div.className = 'recording-item';
    div.innerHTML = `
      <span>Recording ${i + 1}</span>
      <audio controls src="${url}"></audio>
    `;
    list.appendChild(div);
  });
}

// ── Scenario Page ─────────────────────────────────────────────
function loadScenario() {
  const s = scenarios[currentScenarioIndex];
  document.getElementById('scenario-tag').textContent = s.tag;
  document.getElementById('scenario-text').textContent = s.text;
  document.getElementById('scenario-hint').textContent = '💡 Hint: ' + s.hint;
  document.getElementById('sample-answer-box').style.display = 'none';
  document.getElementById('scenario-transcript').textContent = 'Press "Record Response" and speak your answer...';
  document.getElementById('scenario-transcript').style.color = '';
}

function nextScenario() {
  currentScenarioIndex = (currentScenarioIndex + 1) % scenarios.length;
  loadScenario();
  if (isScenarioRecording) stopScenarioRecording();
}

function playScenario() {
  const text = document.getElementById('scenario-text').textContent;
  speakText(text);
}

function showSampleAnswer() {
  const s = scenarios[currentScenarioIndex];
  document.getElementById('sample-answer-text').textContent = s.sample;
  document.getElementById('sample-answer-box').style.display = 'block';
}

function toggleScenarioRecording() {
  if (isScenarioRecording) {
    stopScenarioRecording();
  } else {
    startScenarioRecording();
  }
}

function startScenarioRecording() {
  if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
    alert('Speech recognition is not supported. Please use Chrome.');
    return;
  }

  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  scenarioRecognition = new SR();
  scenarioRecognition.lang = 'en-US';
  scenarioRecognition.continuous = true;
  scenarioRecognition.interimResults = true;

  const el = document.getElementById('scenario-transcript');

  scenarioRecognition.onresult = (event) => {
    let text = '';
    for (const res of event.results) {
      text = res[0].transcript;
      el.style.color = res.isFinal ? '#222' : '#888';
    }
    el.textContent = text;
  };

  scenarioRecognition.onerror = () => {
    el.textContent = 'Could not hear you. Try again.';
  };

  scenarioRecognition.start();
  isScenarioRecording = true;

  const btn = document.getElementById('btn-scenario-record');
  btn.textContent = '⏹ Stop Recording';
  btn.classList.add('recording-active');
}

function stopScenarioRecording() {
  if (scenarioRecognition) scenarioRecognition.stop();
  isScenarioRecording = false;

  const btn = document.getElementById('btn-scenario-record');
  btn.textContent = '🎤 Record Response';
  btn.classList.remove('recording-active');
}

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  renderTopics();
  loadScenario();
});
