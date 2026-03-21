<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Unavailable – {{ $exam->title }}</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/js/all.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.160.1/build/three.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        body {
            background: #000000;
            min-height: 100vh;
        }

        #bg-canvas { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(24px) saturate(160%);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.09);
            box-shadow: 0 8px 40px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.08);
        }

        .icon-ring {
            background: rgba(99,102,241,0.12);
            border: 1px solid rgba(99,102,241,0.25);
            box-shadow: 0 0 40px rgba(99,102,241,0.15);
        }

        @keyframes iconPop {
            0% { transform: scale(0.7); opacity: 0; }
            70% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }
        .icon-pop { animation: iconPop 0.5s cubic-bezier(0.16,1,0.3,1) forwards; }
    </style>
</head>
<body class="text-white min-h-screen flex items-center justify-center p-4">

    <canvas id="bg-canvas"></canvas>

    <div class="max-w-md w-full relative z-10">
        <div class="glass-card rounded-2xl p-8 text-center">

            <!-- Lock Icon -->
            <div class="flex items-center justify-center mb-6">
                <div class="icon-ring w-20 h-20 rounded-full flex items-center justify-center icon-pop">
                    <i class="fas fa-lock text-indigo-400 text-3xl"></i>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-white mb-1">Exam Unavailable</h1>

            <!-- Exam name -->
            <p class="text-sm mb-5" style="color: rgba(255,255,255,0.5)">{{ $exam->title }}</p>

            <!-- Divider -->
            <div class="mb-5" style="border-top: 1px solid rgba(255,255,255,0.07)"></div>

            <!-- Message -->
            <p class="text-sm leading-relaxed" style="color: rgba(255,255,255,0.4)">
                This exam is not published yet or has already been closed.
            </p>

        </div>

        <p class="text-center text-xs mt-5" style="color: rgba(255,255,255,0.22)">
            fiqtest &middot; Coding Assessment Platform
        </p>
    </div>

    <script>
    (() => {
        const canvas = document.getElementById('bg-canvas');
        const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setSize(window.innerWidth, window.innerHeight);
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
        camera.position.set(0, 0, 4.5);
        const COUNT = 2500;
        const positions = new Float32Array(COUNT * 3);
        const colors    = new Float32Array(COUNT * 3);
        const palette = [
            new THREE.Color('#6366f1'), new THREE.Color('#818cf8'),
            new THREE.Color('#4f46e5'), new THREE.Color('#a5b4fc'), new THREE.Color('#c7d2fe'),
        ];
        for (let i = 0; i < COUNT; i++) {
            const theta = Math.random() * Math.PI * 2;
            const phi   = Math.acos(2 * Math.random() - 1);
            const r     = 2.5 + Math.random() * 7;
            positions[i*3]   = r * Math.sin(phi) * Math.cos(theta);
            positions[i*3+1] = r * Math.sin(phi) * Math.sin(theta);
            positions[i*3+2] = r * Math.cos(phi);
            const c = palette[Math.floor(Math.random() * palette.length)];
            colors[i*3] = c.r; colors[i*3+1] = c.g; colors[i*3+2] = c.b;
        }
        const pGeo = new THREE.BufferGeometry();
        pGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        pGeo.setAttribute('color',    new THREE.BufferAttribute(colors, 3));
        const points = new THREE.Points(pGeo, new THREE.PointsMaterial({ size: 0.028, vertexColors: true, transparent: true, opacity: 1.0, sizeAttenuation: true }));
        scene.add(points);
        const torus = new THREE.Mesh(new THREE.TorusGeometry(1.6, 0.45, 16, 100), new THREE.MeshBasicMaterial({ color: 0x6366f1, wireframe: true, transparent: true, opacity: 0.22 }));
        scene.add(torus);
        const ico1 = new THREE.Mesh(new THREE.IcosahedronGeometry(0.55, 1), new THREE.MeshBasicMaterial({ color: 0x818cf8, wireframe: true, transparent: true, opacity: 0.45 }));
        ico1.position.set(-2.8, 0.6, -1.5); scene.add(ico1);
        const ico2 = new THREE.Mesh(new THREE.IcosahedronGeometry(0.4, 1), new THREE.MeshBasicMaterial({ color: 0xa5b4fc, wireframe: true, transparent: true, opacity: 0.40 }));
        ico2.position.set(2.8, -0.5, -1); scene.add(ico2);
        const oct = new THREE.Mesh(new THREE.OctahedronGeometry(0.3, 0), new THREE.MeshBasicMaterial({ color: 0xc7d2fe, wireframe: true, transparent: true, opacity: 0.50 }));
        oct.position.set(0.5, 1.5, 0.5); scene.add(oct);
        const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
        window.addEventListener('mousemove', (e) => { mouse.tx = (e.clientX/window.innerWidth-0.5)*2; mouse.ty = (e.clientY/window.innerHeight-0.5)*2; });
        window.addEventListener('resize', () => { camera.aspect = window.innerWidth/window.innerHeight; camera.updateProjectionMatrix(); renderer.setSize(window.innerWidth, window.innerHeight); });
        let t = 0;
        const animate = () => {
            requestAnimationFrame(animate); t += 0.004;
            mouse.x += (mouse.tx - mouse.x) * 0.06; mouse.y += (mouse.ty - mouse.y) * 0.06;
            points.rotation.y = t*0.07 + mouse.x*0.12; points.rotation.x = mouse.y*0.07;
            torus.rotation.x = t*0.25 + mouse.y*0.1; torus.rotation.y = t*0.18 + mouse.x*0.25;
            ico1.rotation.x = t*0.5; ico1.rotation.y = t*0.35;
            ico2.rotation.x = t*0.3; ico2.rotation.z = t*0.6;
            oct.rotation.x = t*0.8; oct.rotation.y = t*0.5;
            camera.position.x += (mouse.x*0.35 - camera.position.x)*0.04;
            camera.position.y += (-mouse.y*0.25 - camera.position.y)*0.04;
            camera.lookAt(0,0,0); renderer.render(scene, camera);
        };
        animate();
    })();
    </script>
</body>
</html>
