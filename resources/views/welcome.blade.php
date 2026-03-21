<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>fiqtest — Coding Exam Platform</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            width: 100%; height: 100%;
            overflow: hidden;
            background: #020617;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #f1f5f9;
        }

        #canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
        }

        .glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 1;
            pointer-events: none;
        }
        .glow-1 {
            width: 500px; height: 500px;
            background: rgba(99, 102, 241, 0.15);
            top: -100px; left: -100px;
        }
        .glow-2 {
            width: 400px; height: 400px;
            background: rgba(139, 92, 246, 0.1);
            bottom: -100px; right: -50px;
        }

        .hero {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            padding: 2rem;
        }

        .hero h1,
        .hero .subtitle,
        .hero .logo-badge {
            text-align: center;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 9999px;
            padding: 0.35rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #a5b4fc;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.75rem;
            backdrop-filter: blur(8px);
        }

        .logo-badge .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #6366f1;
            box-shadow: 0 0 8px #6366f1;
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.75); }
        }

        h1 {
            font-size: clamp(3.5rem, 9vw, 7rem);
            font-weight: 800;
            line-height: 1.0;
            letter-spacing: -0.04em;
            margin-bottom: 1.25rem;
        }

        h1 .brand {
            background: linear-gradient(135deg, #ffffff 0%, #c7d2fe 50%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        h1 .accent {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: clamp(0.95rem, 2vw, 1.15rem);
            color: #64748b;
            max-width: 420px;
            line-height: 1.7;
            margin-bottom: 2.75rem;
            font-weight: 400;
        }

        .subtitle code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.88em;
            color: #a5b4fc;
            background: rgba(99, 102, 241, 0.1);
            padding: 0.1em 0.45em;
            border-radius: 4px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .exam-list {
            width: 100%;
            max-width: 520px;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .exam-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            text-decoration: none;
            backdrop-filter: blur(12px);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .exam-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.2);
        }

        .exam-card-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            min-width: 0;
            text-align: left;
        }

        .exam-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
        }

        .exam-icon svg {
            width: 18px; height: 18px;
            color: #fff;
            stroke: #fff;
        }

        .exam-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #e2e8f0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .exam-meta {
            font-size: 0.72rem;
            color: #475569;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 0.15rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .exam-arrow {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.2s ease;
        }

        .exam-card:hover .exam-arrow {
            background: rgba(99, 102, 241, 0.25);
        }

        .exam-arrow svg {
            width: 14px; height: 14px;
            stroke: #818cf8;
            transition: transform 0.2s ease;
        }

        .exam-card:hover .exam-arrow svg {
            transform: translateX(2px);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 2rem 1.5rem;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(99, 102, 241, 0.1);
            border-radius: 14px;
            backdrop-filter: blur(12px);
            width: 100%;
            max-width: 520px;
        }

        .empty-state svg {
            width: 36px; height: 36px;
            stroke: #334155;
            margin-bottom: 0.25rem;
        }

        .empty-state .empty-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
        }

        .empty-state .empty-sub {
            font-size: 0.78rem;
            color: #334155;
            font-family: 'JetBrains Mono', monospace;
        }

        .section-label {
            font-size: 0.72rem;
            font-weight: 600;
            color: #ffffff;
            text-shadow:
                0 0 6px rgba(255, 255, 255, 0.9),
                0 0 12px rgba(255, 255, 255, 0.6),
                0 0 24px rgba(255, 255, 255, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            align-self: flex-start;
            margin-bottom: 0.25rem;
            font-family: 'JetBrains Mono', monospace;
            text-align: left;
        }
    </style>
</head>
<body>
    <canvas id="canvas"></canvas>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>

    <section class="hero">
        <div class="logo-badge">
            <span class="dot"></span>
            Made by fiq
        </div>

        <h1>
            <span class="brand">fiq</span><span class="accent">test</span>
        </h1>

        <p class="subtitle">
            Enter your <code>NIM</code> and start your session.<br>
            Your code will be automatically graded.
        </p>

        @if($exams->count())
            <div class="exam-list">
                <p class="section-label">// active exams</p>
                @foreach($exams as $exam)
                    <a href="{{ route('exam.instructions', $exam->slug) }}" class="exam-card">
                        <div class="exam-card-left">
                            <div class="exam-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                                </svg>
                            </div>
                            <div style="min-width:0;">
                                <div class="exam-title">{{ $exam->title }}</div>
                                <div class="exam-meta">{{ $exam->courseOffering->course->name }} · closes {{ $exam->closes_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="exam-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span class="empty-title">No active exams</span>
                <span class="empty-sub">// check back later</span>
            </div>
        @endif
    </section>

    <script src="https://cdn.jsdelivr.net/npm/three@0.160.1/build/three.min.js"></script>
    <script>
    (() => {
        const canvas = document.getElementById('canvas');
        const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(window.innerWidth, window.innerHeight);

        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
        camera.position.set(0, 0, 4.5);

        // --- Particle field ---
        const COUNT = 2500;
        const positions = new Float32Array(COUNT * 3);
        const colors    = new Float32Array(COUNT * 3);

        const palette = [
            new THREE.Color('#6366f1'),
            new THREE.Color('#818cf8'),
            new THREE.Color('#4f46e5'),
            new THREE.Color('#a5b4fc'),
            new THREE.Color('#c7d2fe'),
        ];

        for (let i = 0; i < COUNT; i++) {
            const theta = Math.random() * Math.PI * 2;
            const phi   = Math.acos(2 * Math.random() - 1);
            const r     = 2.5 + Math.random() * 7;

            positions[i * 3]     = r * Math.sin(phi) * Math.cos(theta);
            positions[i * 3 + 1] = r * Math.sin(phi) * Math.sin(theta);
            positions[i * 3 + 2] = r * Math.cos(phi);

            const c = palette[Math.floor(Math.random() * palette.length)];
            colors[i * 3]     = c.r;
            colors[i * 3 + 1] = c.g;
            colors[i * 3 + 2] = c.b;
        }

        const pGeo = new THREE.BufferGeometry();
        pGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        pGeo.setAttribute('color',    new THREE.BufferAttribute(colors, 3));

        const pMat = new THREE.PointsMaterial({
            size: 0.022,
            vertexColors: true,
            transparent: true,
            opacity: 0.85,
            sizeAttenuation: true,
        });

        const points = new THREE.Points(pGeo, pMat);
        scene.add(points);

        // --- Outer wireframe torus ---
        const torus = new THREE.Mesh(
            new THREE.TorusGeometry(1.6, 0.45, 16, 100),
            new THREE.MeshBasicMaterial({ color: 0x6366f1, wireframe: true, transparent: true, opacity: 0.07 })
        );
        scene.add(torus);

        // --- Inner ring ---
        const ring = new THREE.Mesh(
            new THREE.TorusGeometry(0.9, 0.018, 8, 120),
            new THREE.MeshBasicMaterial({ color: 0x818cf8, transparent: true, opacity: 0.5 })
        );
        ring.rotation.x = Math.PI / 3;
        scene.add(ring);

        // --- Icosahedron left ---
        const ico1 = new THREE.Mesh(
            new THREE.IcosahedronGeometry(0.55, 1),
            new THREE.MeshBasicMaterial({ color: 0x4f46e5, wireframe: true, transparent: true, opacity: 0.18 })
        );
        ico1.position.set(-2.8, 0.6, -1.5);
        scene.add(ico1);

        // --- Icosahedron right ---
        const ico2 = new THREE.Mesh(
            new THREE.IcosahedronGeometry(0.4, 1),
            new THREE.MeshBasicMaterial({ color: 0x818cf8, wireframe: true, transparent: true, opacity: 0.15 })
        );
        ico2.position.set(2.8, -0.5, -1);
        scene.add(ico2);

        // --- Octahedron center-ish ---
        const oct = new THREE.Mesh(
            new THREE.OctahedronGeometry(0.3, 0),
            new THREE.MeshBasicMaterial({ color: 0xa5b4fc, wireframe: true, transparent: true, opacity: 0.2 })
        );
        oct.position.set(0.5, 1.5, 0.5);
        scene.add(oct);

        // Mouse tracking
        const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
        window.addEventListener('mousemove', (e) => {
            mouse.tx = (e.clientX / window.innerWidth  - 0.5) * 2;
            mouse.ty = (e.clientY / window.innerHeight - 0.5) * 2;
        });

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        let t = 0;
        const animate = () => {
            requestAnimationFrame(animate);
            t += 0.004;

            // Smooth mouse lerp
            mouse.x += (mouse.tx - mouse.x) * 0.06;
            mouse.y += (mouse.ty - mouse.y) * 0.06;

            points.rotation.y = t * 0.07 + mouse.x * 0.12;
            points.rotation.x = mouse.y * 0.07;

            torus.rotation.x = t * 0.25 + mouse.y * 0.1;
            torus.rotation.y = t * 0.18 + mouse.x * 0.25;

            ring.rotation.z  = t * 0.45;
            ring.rotation.y  = mouse.x * 0.2;

            ico1.rotation.x  = t * 0.5;
            ico1.rotation.y  = t * 0.35;

            ico2.rotation.x  = t * 0.3;
            ico2.rotation.z  = t * 0.6;

            oct.rotation.x   = t * 0.8;
            oct.rotation.y   = t * 0.5;

            camera.position.x += (mouse.x * 0.35 - camera.position.x) * 0.04;
            camera.position.y += (-mouse.y * 0.25 - camera.position.y) * 0.04;
            camera.lookAt(0, 0, 0);

            renderer.render(scene, camera);
        };

        animate();
    })();
    </script>
</body>
</html>
