<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <title>For - You ;) </title>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
    <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body {
        background: #020205; overflow: hidden;
        font-family: monospace; color: #66fcf1;
        -webkit-user-select: none; user-select: none;
      }
      #container { position: relative; width: 100vw; height: 100vh; }
      canvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; }
      #offCanvas { display: none; }
      video { display: none; }
      #loading {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        font-size: 13px; z-index: 3; text-align: center;
        text-shadow: 0 0 8px #00f0ff; width: 80%;
      }
      #hud {
        position: absolute; top: 12px; left: 12px; z-index: 3;
        font-size: 10px; pointer-events: none;
        background: rgba(2,2,5,0.8); padding: 8px;
        border: 1px solid #1f2833; border-radius: 4px;
        line-height: 1.5; max-width: 85%;
      }
      .hi { color: #ffd700; font-weight: bold; }
    </style>
  </head>
  <body>
    <div id="container">
      <div id="loading">MINTA IZIN KAMERA...<br>PASTIKAN JARINGAN INTERNET STABIL</div>
      <div id="hud">
        [COSMIC ENGINE - OPTIMIZED]<br>
        TEXT: <span id="hudText" class="hi">I love you</span><br>
        (Tap layar untuk ganti teks!)<br>
        --------------------------------<br>
        ☝️ 1 jari &rarr; PANDAKONG<br>
        ✌️ 2 jari &rarr; TEKS CUSTOM<br>
        👍 Jempol &rarr; LOVE HEART<br>
        🤚 4 jari &rarr; SUPERNOVA<br>
        ✊ Kepal &rarr; SATURNUS<br>
        STATE: <span id="hudState" class="hi">STANDBY</span>
      </div>
      <video id="webcam" autoplay playsinline></video>
      <canvas id="particleCanvas"></canvas>
      <canvas id="offCanvas"></canvas>
    </div>

    <script>
    // ── SETUP ────────────────────────────────────────────────────────────────────
    const video   = document.getElementById("webcam");
    const pCanvas = document.getElementById("particleCanvas");
    const pCtx    = pCanvas.getContext("2d");
    const offC    = document.getElementById("offCanvas");
    const offCtx  = offC.getContext("2d");
    const loading = document.getElementById("loading");
    const hudText  = document.getElementById("hudText");
    const hudState = document.getElementById("hudState");

    const NUM_STARS = 1800;
    let stars = [];
    let textCoords  = [];
    let pandaCoords = [];
    let heartCoords = []; // OPT#3: generate sekali, tidak di-loop ulang

    let currentText  = "I love you";
    let activeHands  = [];
    let currentState = "standby";

    // OPT#5: Gesture debounce — state hanya berubah setelah 5 frame konsisten
    let pendingState  = "standby";
    let pendingFrames = 0;
    const DEBOUNCE_FRAMES = 5;

    // OPT#7: HUD dirty flag — hanya update DOM saat ada perubahan
    let lastHudState = "";
    let lastHudText  = "";

    // ── HEART COORDS: generate sekali saja ──────────────────────────────────────
    (function generateHeart() {
      heartCoords = [];
      for (let a = 0; a < Math.PI * 2; a += 0.01) {
        let hx = 16 * Math.pow(Math.sin(a), 3);
        let hy = -(13*Math.cos(a) - 5*Math.cos(2*a) - 2*Math.cos(3*a) - Math.cos(4*a));
        heartCoords.push({ bx: hx * 14, by: hy * 14 });
      }
    })();

    // ── INPUT ────────────────────────────────────────────────────────────────────
    window.addEventListener("keydown", (e) => {
      if (e.key === "Backspace") currentText = currentText.slice(0, -1);
      else if (e.key.length === 1) {
        if (currentText === "I love you") currentText = "";
        currentText += e.key;
      }
      generatePixelCoords();
      hudText.textContent = currentText || "[Kosong]";
    });

    window.addEventListener("click", () => {
      let inp = prompt("Ketik teks baru ✌️:", currentText);
      if (inp !== null && inp.trim() !== "") {
        currentText = inp.trim();
        generatePixelCoords();
        hudText.textContent = currentText;
      }
    });

    // ── GENERATE PIXEL COORDS ────────────────────────────────────────────────────
    // OPT#6: dipanggil juga saat resize
    function generatePixelCoords() {
      // -- Teks custom
      offC.width = 900; offC.height = 200;
      offCtx.clearRect(0, 0, 900, 200);
      offCtx.font = "bold 85px sans-serif";
      offCtx.fillStyle = "white";
      offCtx.textAlign = "center";
      offCtx.textBaseline = "middle";
      offCtx.fillText(currentText || " ", 450, 100);
      const td = offCtx.getImageData(0, 0, 900, 200).data;
      textCoords = [];
      for (let y = 0; y < 200; y += 4)
        for (let x = 0; x < 900; x += 4)
          if (td[(y*900+x)*4+3] > 100)
            textCoords.push({ ox: (x-450)*1.5, oy: (y-100)*1.5 });

      // -- PANDAKONG pixel-map
      offC.width = 1100; offC.height = 220;
      offCtx.clearRect(0, 0, 1100, 220);
      offCtx.font = "bold 130px sans-serif";
      offCtx.fillStyle = "white";
      offCtx.textAlign = "center";
      offCtx.textBaseline = "middle";
      offCtx.fillText("PANDAKONG", 550, 110);
      const pd = offCtx.getImageData(0, 0, 1100, 220).data;
      pandaCoords = [];
      for (let y = 0; y < 220; y += 4)
        for (let x = 0; x < 1100; x += 4)
          if (pd[(y*1100+x)*4+3] > 100)
            pandaCoords.push({ ox: (x-550)*1.3, oy: (y-110)*1.3 });
    }
    generatePixelCoords();

    // ── RESIZE ───────────────────────────────────────────────────────────────────
    function resizeCanvas() {
      pCanvas.width  = window.innerWidth;
      pCanvas.height = window.innerHeight;
      generatePixelCoords(); // OPT#6: recalc saat ukuran layar berubah
      if (stars.length === 0) initCosmos();
    }
    window.addEventListener("resize", resizeCanvas);

    // ── STAR CLASS ───────────────────────────────────────────────────────────────
    class Star {
      constructor(id) {
        this.id   = id;
        this.size = Math.random() * 2.2 + 0.6;
        this.x    = Math.random() * pCanvas.width;
        this.y    = Math.random() * pCanvas.height;
        // OPT#2: alpha pre-computed via sin, tidak Math.random() tiap frame
        this.alphaOffset = Math.random() * Math.PI * 2;
        // OPT#8: warna pre-assign per id, tidak bikin string tiap frame
        this.standbyColor = ["#ffd700","#39ff14","#00f0ff","#ffffff"][id % 4];
        this.exFX = 0; this.exFY = 0;
        this.reset();
      }

      reset() {
        const a   = Math.random() * Math.PI * 2;
        const r   = Math.pow(Math.random(), 1.2) * (Math.min(pCanvas.width, pCanvas.height) * 0.45);
        const na  = Math.random() * Math.PI * 2;
        const nr  = Math.random() * 30;
        this.sbOX = Math.cos(a)*r + Math.cos(na)*nr;
        this.sbOY = Math.sin(a)*r + Math.sin(na)*nr;
        this.dX   = (Math.random()-0.5)*0.3;
        this.dY   = (Math.random()-0.5)*0.3;

        this.satAngle = Math.random() * Math.PI * 2;
        this.isRing   = Math.random() < 0.65;
        this.satR     = Math.random()*40 + Math.min(pCanvas.width, pCanvas.height)*0.4;
        this.satNX    = Math.cos(na) * (Math.random()*18);
        this.satNY    = Math.sin(na) * (Math.random()*18);

        this.tNX = Math.cos(na)*(Math.random()*14);
        this.tNY = Math.sin(na)*(Math.random()*14);
        this.hNX = Math.cos(na)*(Math.random()*35);
        this.hNY = Math.sin(na)*(Math.random()*35);
        this.pNX = (Math.random()-0.5)*4;
        this.pNY = (Math.random()-0.5)*4;

        this.exFX = 0; this.exFY = 0;
      }

      update(t, hand, state, mScale) {
        // OPT#2: alpha pakai sin — smooth, zero allocation
        this.alpha = 0.3 + 0.7 * (0.5 + 0.5 * Math.sin(t * 3 + this.alphaOffset));

        let fx = pCanvas.width/2, fy = pCanvas.height/2;
        const hx = hand ? hand.x : fx;
        const hy = hand ? hand.y : fy;

        if (state !== "supernova") { this.exFX = 0; this.exFY = 0; }

        if (state === "panda" && pandaCoords.length > 0) {
          this.color = "#ebf5ff"; // OPT#8: string statis, tidak dibuat ulang
          const d = pandaCoords[this.id % pandaCoords.length];
          fx = hx + d.ox*mScale + this.pNX + Math.sin(t*2+this.id)*1.5;
          fy = hy + d.oy*mScale + this.pNY + Math.cos(t*2+this.id)*1.5;

        } else if (state === "text" && textCoords.length > 0) {
          this.color = "#79d7ff";
          const d = textCoords[this.id % textCoords.length];
          fx = hx + (d.ox+this.tNX)*mScale + Math.sin(t*2.5+this.id)*3;
          fy = hy + (d.oy+this.tNY)*mScale + Math.cos(t*2.5+this.id)*3;

        } else if (state === "heart" && heartCoords.length > 0) {
          this.color = "#7fff00";
          const d = heartCoords[this.id % heartCoords.length];
          fx = hx + (d.bx+this.hNX)*mScale + Math.sin(t*3+this.id)*3;
          fy = hy + (d.by+this.hNY)*mScale + Math.cos(t*3+this.id)*3;

        } else if (state === "saturn") {
          this.color = "#4ce2ff";
          if (this.isRing) {
            fx = hx + Math.cos(this.satAngle)*this.satR + this.satNX + Math.sin(t*2+this.id)*2;
            fy = hy + Math.sin(this.satAngle)*(this.satR*0.25) + this.satNY + Math.cos(t*2+this.id)*2;
            this.satAngle += 0.035;
          } else {
            const rMax = Math.min(pCanvas.width, pCanvas.height)*0.18;
            const sr   = (this.id%95)*(rMax/95);
            const u    = (this.id%100)*(Math.PI*2/100) + t*0.3;
            fx = hx + Math.cos(u)*sr;
            fy = hy + Math.sin(u)*sr;
          }

        } else if (state === "supernova" && hand) {
          this.color = "#ff7a96";
          if (this.exFX === 0 && this.exFY === 0) {
            const ang = Math.atan2(this.y - hand.y, this.x - hand.x);
            const push = Math.random()*20 + 8;
            this.exFX = Math.cos(ang)*push;
            this.exFY = Math.sin(ang)*push;
          }
          this.x += this.exFX;
          this.y += this.exFY;
          if (this.x < -60 || this.x > pCanvas.width+60 || this.y < -60 || this.y > pCanvas.height+60)
            this.reset();
          return;

        } else {
          // STANDBY
          this.color = this.standbyColor;
          this.sbOX += this.dX; this.sbOY += this.dY;
          const bd = Math.min(pCanvas.width, pCanvas.height)*0.6;
          if (Math.abs(this.sbOX) > bd) this.dX *= -1;
          if (Math.abs(this.sbOY) > bd) this.dY *= -1;
          fx = hx + this.sbOX;
          fy = hy + this.sbOY;
        }

        if (isNaN(fx) || isNaN(fy)) { this.reset(); return; }

        // OPT#9: easing berbeda per state untuk transisi lebih natural
        const ease = state === "standby" ? 0.06
                   : state === "supernova" ? 0.35
                   : state === "panda"     ? 0.18
                   : 0.22;
        this.x += (fx - this.x) * ease;
        this.y += (fy - this.y) * ease;
      }
    }

    function initCosmos() {
      stars = [];
      for (let i = 0; i < NUM_STARS; i++) stars.push(new Star(i));
    }

    function hardReset() {
      stars.forEach(s => { s.exFX = 0; s.exFY = 0; if (currentState === "standby") s.reset(); });
    }

    // ── POSE ANALYSIS ────────────────────────────────────────────────────────────
    function analyzePose(lm) {
      const iU = lm[8].y  < lm[6].y;
      const mU = lm[12].y < lm[10].y;
      const rU = lm[16].y < lm[14].y;
      const pU = lm[20].y < lm[18].y;
      if (iU && mU && rU && pU)  return "supernova";
      if (iU && !mU && !rU && !pU) return "panda";
      const tU = lm[4].y < lm[3].y && lm[4].y < lm[5].y;
      if (tU && !iU && !mU && !rU && !pU) return "heart";
      if (!iU && !mU && !rU && !pU)       return "saturn";
      if (iU && mU && !rU && !pU)         return "text";
      return "standby";
    }

    // ── MEDIAPIPE ────────────────────────────────────────────────────────────────
    const handsModel = new Hands({
      locateFile: f => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${f}`
    });
    handsModel.setOptions({
      maxNumHands: 1, modelComplexity: 0,
      minDetectionConfidence: 0.55, minTrackingConfidence: 0.55
    });

    handsModel.onResults((results) => {
      if (loading) loading.style.display = "none";
      activeHands = [];

      if (results.multiHandLandmarks?.length > 0) {
        const lm  = results.multiHandLandmarks[0];
        const raw = analyzePose(lm);

        // OPT#5: Debounce gesture — cegah flip-flop
        if (raw === pendingState) {
          pendingFrames++;
          if (pendingFrames >= DEBOUNCE_FRAMES && raw !== currentState) {
            currentState = raw;
            hardReset();
          }
        } else {
          pendingState  = raw;
          pendingFrames = 1;
        }

        activeHands.push({
          x: (1 - lm[9].x) * pCanvas.width,
          y: lm[9].y * pCanvas.height,
          state: currentState
        });
      } else {
        pendingState  = "standby";
        pendingFrames = 0;
        if (currentState !== "standby") { currentState = "standby"; hardReset(); }
      }

      // OPT#7: Update HUD hanya jika ada perubahan
      const newState = activeHands[0]?.state ?? "standby";
      const labelMap = {
        panda:"PANDAKONG 📜", text:"TEKS ✌️", heart:"LOVE ❤️",
        saturn:"SATURNUS ✊", supernova:"SUPERNOVA 🤚", standby:"STANDBY ✨"
      };
      const label = labelMap[newState] ?? newState.toUpperCase();
      if (label !== lastHudState) { hudState.textContent = label; lastHudState = label; }
      if (currentText !== lastHudText) { hudText.textContent = currentText; lastHudText = currentText; }
    });

    const cam = new Camera(video, {
      onFrame: async () => { await handsModel.send({ image: video }); },
      facingMode: "user", width: 480, height: 360
    });
    cam.start();

    resizeCanvas();

    // ── RENDER LOOP ──────────────────────────────────────────────────────────────
    // OPT#1: Batch draw per warna — kurangi state changes pada ctx
    // OPT#4: compositeOperation fade trail (lebih GPU-friendly daripada fillRect)
    function render() {
      const t = performance.now() * 0.001;
      const hand  = activeHands[0] ?? null;
      const state = currentState;
      const mScale = Math.min(1.0, pCanvas.width / 700);

      // Fade trail
      pCtx.globalCompositeOperation = "destination-out";
      pCtx.fillStyle = "rgba(0,0,0,0.35)";
      pCtx.fillRect(0, 0, pCanvas.width, pCanvas.height);
      pCtx.globalCompositeOperation = "source-over";

      // Glow — dimatikan saat panda & text agar tulisan partikel tidak tertutup
      if (state !== "supernova" && state !== "panda" && state !== "text") {
        const gx = hand ? hand.x : pCanvas.width/2;
        const gy = hand ? hand.y : pCanvas.height/2;
        const gr = Math.min(pCanvas.width, pCanvas.height) * 0.28;
        const gc = state === "panda" ? "rgba(235,245,255," : "rgba(0,240,255,";
        const g  = pCtx.createRadialGradient(gx, gy, 0, gx, gy, gr);
        g.addColorStop(0,   gc+"0.9)");
        g.addColorStop(0.3, gc+"0.3)");
        g.addColorStop(1,   gc+"0)");
        pCtx.fillStyle = g;
        pCtx.beginPath();
        pCtx.arc(gx, gy, gr, 0, Math.PI*2);
        pCtx.fill();
      }

      // OPT#1: Update semua bintang dulu
      for (let i = 0; i < NUM_STARS; i++) stars[i].update(t, hand, state, mScale);

      // OPT#1: Batch draw — grouping per warna
      const colorGroups = {};
      for (let i = 0; i < NUM_STARS; i++) {
        const s = stars[i];
        if (!colorGroups[s.color]) colorGroups[s.color] = [];
        colorGroups[s.color].push(s);
      }
      for (const color in colorGroups) {
        const group = colorGroups[color];
        pCtx.fillStyle = color;
        for (let j = 0; j < group.length; j++) {
          const s = group[j];
          pCtx.globalAlpha = s.alpha;
          pCtx.beginPath();
          pCtx.arc(s.x, s.y, s.size, 0, Math.PI*2);
          pCtx.fill();
        }
      }
      pCtx.globalAlpha = 1;

      requestAnimationFrame(render);
    }
    requestAnimationFrame(render);
    </script>
  </body>
</html>