const subtitleEl = document.getElementById('subtitle');
const text = lang === 'ru' ? 'Внутренняя ошибка сервера' : 'Internal Server Error';
let ci = 0;
function typeChar() {
    if (ci < text.length) {
        subtitleEl.textContent += text[ci++];
        setTimeout(typeChar, 45 + Math.random() * 35);
    }
}
setTimeout(typeChar, 500);

const logBlock = document.getElementById('logBlock');
const logs = [
    { text: '[ERROR]', cls: 'err', rest: lang === 'ru' ? ' Процесс неожиданно завершён' : ' Process terminated unexpectedly' },
    { text: '[SYS]',   cls: 'accent', rest: lang === 'ru' ? ' Попытка автоматического восстановления...' : ' Attempting auto-recovery...' },
    { text: '[SYS]',   cls: 'accent', rest: lang === 'ru' ? ' Статус: диагностика' : ' Status: diagnosing' },
];

logs.forEach((log, i) => {
    setTimeout(() => {
        const line = document.createElement('div');
        line.className = 'log-line';
        line.style.animationDelay = '0s';
        line.innerHTML = `<span class="${log.cls}">${log.text}</span>${log.rest}`;
        logBlock.appendChild(line);
    }, 1800 + i * 600);
});

function updateTimestamp() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('timestamp').textContent =
        `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} UTC+${-now.getTimezoneOffset()/60}`;
}
updateTimestamp();
setInterval(updateTimestamp, 1000);

const canvas = document.getElementById('bgCanvas');
const ctx = canvas.getContext('2d');
let W, H;

function resize() {
    W = canvas.width = window.innerWidth;
    H = canvas.height = window.innerHeight;
}
resize();
window.addEventListener('resize', resize);

const particles = [];
const PARTICLE_COUNT = 80;

class Particle {
    constructor() {
        this.x = Math.random() * W;
        this.y = Math.random() * H;
        this.size = Math.random() * 2 + 0.5;
        this.speedX = (Math.random() - 0.5) * 0.3;
        this.speedY = -(Math.random() * 0.3 + 0.1);
        this.opacity = Math.random() * 0.4 + 0.05;
    }
    update() {
        this.x += this.speedX;
        this.y += this.speedY;

        if (this.y < -10) { this.y = H + 10; this.x = Math.random() * W; }
        if (this.x < -10) this.x = W + 10;
        if (this.x > W + 10) this.x = -10;
    }
    draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(23, 100, 206, ${this.opacity})`;
        ctx.fill();
    }
}

for (let i = 0; i < PARTICLE_COUNT; i++) particles.push(new Particle());

function drawConnections() {
    for (let i = 0; i < particles.length; i++) {
        for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 120) {
                const alpha = (1 - dist / 120) * 0.08;
                ctx.beginPath();
                ctx.moveTo(particles[i].x, particles[i].y);
                ctx.lineTo(particles[j].x, particles[j].y);
                ctx.strokeStyle = `rgba(23, 100, 206, ${alpha})`;
                ctx.lineWidth = 0.5;
                ctx.stroke();
            }
        }
    }
}

function animate() {
    ctx.clearRect(0, 0, W, H);

    ctx.strokeStyle = 'rgba(23, 100, 206, 0.025)';
    ctx.lineWidth = 1;
    const gridSize = 60;
    for (let x = 0; x < W; x += gridSize) {
        ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, H); ctx.stroke();
    }
    for (let y = 0; y < H; y += gridSize) {
        ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
    }

    particles.forEach(p => { p.update(); p.draw(); });
    drawConnections();

    requestAnimationFrame(animate);
}
animate();

document.addEventListener('mousemove', (e) => {
    const x = (e.clientX / W - 0.5) * 8;
    const y = (e.clientY / H - 0.5) * 8;
    document.getElementById('errorCode').style.transform = `translate(${x}px, ${y}px)`;
});