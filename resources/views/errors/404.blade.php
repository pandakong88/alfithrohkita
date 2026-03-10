<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <title>MAINTENIS - PANDAKONG</title>

        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Orbitron:wght@900&family=VT323&display=swap" rel="stylesheet" />

        <style>
            :root {
                --court-color: #34495e;
                --item-box: #f1c40f;
                --player-color: #00f2ff;
                --bot-color: #ff007b;
                --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            }

            * { box-sizing: border-box; touch-action: none; -webkit-font-smoothing: antialiased; }

            body {
                margin: 0; padding: 0;
                background: var(--bg-gradient);
                font-family: "Plus Jakarta Sans", sans-serif;
                height: 100vh; width: 100vw;
                display: flex; flex-direction: column; align-items: center; justify-content: center;
                overflow: hidden; color: #2c3e50;
            }

            /* Badge Status di Pojok */
            .error-badge {
                position: absolute;
                top: 20px;
                right: 20px;
                background: #1a2a3a;
                color: #ff007b;
                padding: 10px 20px;
                border-radius: 8px;
                font-family: "Orbitron", sans-serif;
                font-size: 0.9rem;
                border: 2px solid #ff007b;
                box-shadow: 0 0 15px rgba(255, 0, 123, 0.4);
                z-index: 100;
                letter-spacing: 2px;
            }

            .header { text-align: center; margin-bottom: 20px; z-index: 10; padding: 0 15px; }
            .header h1 {
                font-size: clamp(1.1rem, 5vw, 1.8rem);
                margin: 0; color: #1a2a3a; text-transform: uppercase;
            }
            .header .status {
                font-family: "VT323"; font-size: 1.1rem;
                color: #e67e22; margin-top: 5px;
            }

            .scene { perspective: 1200px; width: 95vw; max-width: 850px; }

            #game-container {
                width: 100%; height: 65vh; max-height: 480px;
                background: var(--court-color);
                border: 6px solid #ecf0f1; border-radius: 12px;
                transform: rotateX(20deg);
                box-shadow: 0 25px 0 #2c3e50, 0 40px 60px rgba(0, 0, 0, 0.15);
                position: relative; overflow: hidden;
            }

            canvas { width: 100%; height: 100%; display: block; }

            .skill-notif {
                position: absolute; top: 15%; left: 50%; transform: translateX(-50%);
                font-family: "Orbitron", sans-serif; font-size: 1.8rem;
                color: #fff; text-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
                pointer-events: none; opacity: 0; z-index: 20;
                transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            .footer { margin-top: 40px; font-family: "VT323"; font-size: 1.2rem; color: #7f8c8d; }
            .footer b { color: #2c3e50; letter-spacing: 1px; }
        </style>
    </head>
    <body>
        {{-- Badge Penanda Kode Error --}}
        <div class="error-badge">
            @if(isset($exception))
                CODE: {{ $exception->getStatusCode() }}
            @else
                CODE: 503
            @endif
        </div>

        <div class="header">
            <h1>
                @if(isset($exception))
                    @if($exception->getStatusCode() == 503)
                        Developer Sedang Maintenis, Sabar ya?
                    @elseif($exception->getStatusCode() == 404)
                        Halaman Hilang, Main Tenis Aja Yuk?
                    @else
                        Ada Bug Nyasar, Santai Dulu Sejenak!
                    @endif
                @else
                    Developer Sedang Maintenis, Sabar ya?
                @endif
            </h1>
            <div class="status">HIT THE [?] BOX TO ACTIVATE KAGE BUNSHIN!</div>
        </div>

        <div class="scene">
            <div id="skill-text" class="skill-notif">KAGE BUNSHIN!</div>
            <div id="game-container">
                <canvas id="pongCanvas"></canvas>
            </div>
        </div>

        <div class="footer">TIM PENGEMBANG: <b>PANDAKONG</b></div>

        <script>
            const canvas = document.getElementById("pongCanvas");
            const ctx = canvas.getContext("2d");
            const container = document.getElementById("game-container");
            const skillText = document.getElementById("skill-text");

            let width, height, unit;
            let balls = [];
            let itemBox = { x: 0, y: 0, size: 0, active: false, pulse: 0 };

            const paddle = { w: 0, h: 0 };
            const player = { y: 0, score: 0 };
            const bot = { y: 0, score: 0 };

            function init() {
                width = container.clientWidth;
                height = container.clientHeight;
                canvas.width = width;
                canvas.height = height;
                unit = width / 100;
                paddle.w = unit * 2;
                paddle.h = unit * 16;
                resetGame();
                spawnItem();
            }

            function createBall(x, y, dx, dy, speed, type = "normal", color = "#f1c40f") {
                return { x, y, dx, dy, speed, radius: unit * 1.5, type, color };
            }

            function resetGame() {
                balls = [createBall(width / 2, height / 2, unit * 0.9, (Math.random() - 0.5) * unit, unit * 0.9)];
                spawnItem();
            }

            function spawnItem() {
                itemBox.size = unit * 6;
                itemBox.x = width / 2 - itemBox.size / 2;
                itemBox.y = Math.random() * (height - itemBox.size * 2) + itemBox.size;
                itemBox.active = true;
            }

            function triggerSkill(ball) {
                const skills = ["clone", "fire", "bolt", "ghost"];
                const type = skills[Math.floor(Math.random() * skills.length)];

                itemBox.active = false;
                skillText.style.opacity = 1;
                skillText.style.transform = "translateX(-50%) scale(1.2)";

                setTimeout(() => {
                    skillText.style.opacity = 0;
                    skillText.style.transform = "translateX(-50%) scale(1)";
                    spawnItem();
                }, 2000);

                if (type === "clone") {
                    skillText.innerText = "KAGE BUNSHIN! 💨";
                    for (let i = 0; i < 3; i++) {
                        balls.push(createBall(ball.x, ball.y, -ball.dx, (Math.random() - 0.5) * unit * 5, ball.speed, "normal", "#ecf0f1"));
                    }
                } else if (type === "fire") {
                    skillText.innerText = "BOLA API! 🔥";
                    ball.type = "fire"; ball.color = "#e67e22"; ball.speed *= 1.6;
                } else if (type === "bolt") {
                    skillText.innerText = "PETIR! ⚡";
                    ball.type = "bolt"; ball.color = "#00f2ff"; ball.speed *= 2.2;
                } else {
                    skillText.innerText = "GHAIB! 👻";
                    ball.type = "ghost"; ball.color = "rgba(255,255,255,0.4)";
                }
            }

            function handleInput(e) {
                const rect = canvas.getBoundingClientRect();
                const clientY = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
                player.y = clientY * (height / rect.height) - paddle.h / 2;
            }

            window.addEventListener("mousemove", handleInput);
            window.addEventListener("touchmove", (e) => { handleInput(e); e.preventDefault(); }, { passive: false });
            window.addEventListener("resize", init);

            function update() {
                let leadBall = balls.length > 0 ? balls.reduce((prev, curr) => (curr.x > prev.x ? curr : prev), balls[0]) : null;
                if(leadBall) bot.y += (leadBall.y - (bot.y + paddle.h / 2)) * 0.1;

                balls.forEach((ball, index) => {
                    ball.x += ball.dx; ball.y += ball.dy;
                    if (ball.y < 0 || ball.y > height) ball.dy *= -1;

                    if (itemBox.active && ball.x > itemBox.x && ball.x < itemBox.x + itemBox.size && ball.y > itemBox.y && ball.y < itemBox.y + itemBox.size) {
                        triggerSkill(ball);
                    }

                    let p = ball.x < width / 2 ? player : bot;
                    let pX = ball.x < width / 2 ? 20 : width - 20 - paddle.w;

                    if (ball.x + ball.radius > pX && ball.x - ball.radius < pX + paddle.w && ball.y > p.y && ball.y < p.y + paddle.h) {
                        let collidePoint = (ball.y - (p.y + paddle.h / 2)) / (paddle.h / 2);
                        let angle = (Math.PI / 4) * collidePoint;
                        ball.dx = (ball.x < width / 2 ? 1 : -1) * ball.speed * Math.cos(angle);
                        ball.dy = ball.speed * Math.sin(angle);
                        ball.speed += unit * 0.05;
                    }

                    if (ball.x < 0 || ball.x > width) {
                        if (balls.length > 1) balls.splice(index, 1);
                        else {
                            if (ball.x < 0) bot.score++; else player.score++;
                            resetGame();
                        }
                    }
                });
                itemBox.pulse += 0.1;
            }

            function draw() {
                ctx.fillStyle = "#34495e";
                ctx.fillRect(0, 0, width, height);

                ctx.strokeStyle = "rgba(255,255,255,0.1)";
                ctx.lineWidth = 2;
                ctx.strokeRect(unit * 2, unit * 2, width - unit * 4, height - unit * 4);
                ctx.beginPath(); ctx.moveTo(width / 2, 0); ctx.lineTo(width / 2, height); ctx.stroke();

                if (itemBox.active) {
                    let s = itemBox.size + Math.sin(itemBox.pulse) * 5;
                    ctx.fillStyle = "#f1c40f";
                    ctx.shadowBlur = 15; ctx.shadowColor = "#f1c40f";
                    ctx.fillRect(itemBox.x, itemBox.y, s, s);
                    ctx.fillStyle = "#fff"; ctx.font = `bold ${s * 0.7}px Arial`;
                    ctx.textAlign = "center"; ctx.fillText("?", itemBox.x + s / 2, itemBox.y + s * 0.75);
                    ctx.shadowBlur = 0;
                }

                balls.forEach((ball) => {
                    ctx.fillStyle = ball.color;
                    ctx.shadowBlur = ball.type !== "normal" ? 20 : 0; ctx.shadowColor = ball.color;
                    if (ball.type === "fire") {
                        ctx.font = `${ball.radius * 3.5}px serif`;
                        ctx.fillText("🔥", ball.x, ball.y + ball.radius);
                    } else {
                        ctx.beginPath(); ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2); ctx.fill();
                    }
                });

                ctx.shadowBlur = 10;
                ctx.fillStyle = "#00f2ff"; ctx.shadowColor = "#00f2ff";
                ctx.fillRect(20, player.y, paddle.w, paddle.h);
                ctx.fillStyle = "#ff007b"; ctx.shadowColor = "#ff007b";
                ctx.fillRect(width - 20 - paddle.w, bot.y, paddle.w, paddle.h);

                ctx.font = `800 ${unit * 10}px Orbitron`;
                ctx.fillStyle = "rgba(255,255,255,0.05)";
                ctx.textAlign = "center";
                ctx.fillText(`${player.score} : ${bot.score}`, width / 2, height / 2 + unit * 4);

                requestAnimationFrame(() => { update(); draw(); });
            }
            init(); draw();
        </script>
    </body>
</html>