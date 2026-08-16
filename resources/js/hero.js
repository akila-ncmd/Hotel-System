/**
 * Diamond Shine — WebGL hero.
 *
 * One idea, executed cleanly: the hero photograph is viewed *through* a slowly
 * moving diamond. A domain-warped noise field refracts the image, and the three
 * colour channels are sampled at slightly different offsets so the distortion
 * splits light into a prism the way a real gemstone does. A slow specular sweep
 * crosses the surface on a long cycle.
 *
 * Deliberately not a pile of effects: no particles, no post-processing chain,
 * no physics. Everything below is one full-screen plane and one fragment shader.
 *
 * Degradation, in order:
 *   - `prefers-reduced-motion`  -> renders a single static frame, then stops.
 *   - no WebGL context          -> module exits, the CSS parallax layers remain.
 *   - narrow viewport           -> lower pixel ratio and a cheaper noise field.
 *   - tab hidden / hero offscreen -> render loop pauses entirely.
 */

import {
    Mesh,
    OrthographicCamera,
    PlaneGeometry,
    Scene,
    ShaderMaterial,
    TextureLoader,
    Vector2,
    WebGLRenderer,
} from 'three';

const vertexShader = /* glsl */ `
    varying vec2 vUv;

    void main() {
        vUv = uv;
        gl_Position = vec4(position.xy, 0.0, 1.0);
    }
`;

const fragmentShader = /* glsl */ `
    precision highp float;

    uniform sampler2D uTexture;
    uniform vec2  uResolution;
    uniform vec2  uImageSize;
    uniform vec2  uPointer;
    uniform float uTime;
    uniform float uDispersion;
    uniform float uOctaves;

    varying vec2 vUv;

    // --- cheap value noise + fbm ------------------------------------------
    // Gradient noise would be prettier but this holds 60fps on mid-range
    // phones, which matters more than the last 5% of visual quality.

    float hash(vec2 p) {
        return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453123);
    }

    float noise(vec2 p) {
        vec2 i = floor(p);
        vec2 f = fract(p);
        vec2 u = f * f * (3.0 - 2.0 * f);   // smoothstep interpolation

        return mix(
            mix(hash(i + vec2(0.0, 0.0)), hash(i + vec2(1.0, 0.0)), u.x),
            mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x),
            u.y
        );
    }

    float fbm(vec2 p) {
        float value = 0.0;
        float amplitude = 0.5;

        // Loop bound must be a constant in GLSL ES 1.0, so the octave count is
        // applied as a weight instead — lets the CPU pick 2 or 4 octaves.
        for (int i = 0; i < 4; i++) {
            if (float(i) >= uOctaves) break;
            value += amplitude * noise(p);
            p *= 2.02;
            amplitude *= 0.5;
        }

        return value;
    }

    // Cover-fit the texture, the GLSL equivalent of background-size: cover.
    vec2 coverUv(vec2 uv) {
        float screenAspect = uResolution.x / uResolution.y;
        float imageAspect  = uImageSize.x / uImageSize.y;
        vec2 scale = screenAspect > imageAspect
            ? vec2(1.0, imageAspect / screenAspect)
            : vec2(screenAspect / imageAspect, 1.0);
        return (uv - 0.5) * scale + 0.5;
    }

    void main() {
        vec2 uv = coverUv(vUv);

        // Domain warp: noise sampled through noise. This is what gives the
        // distortion its slow, liquid, non-repeating character.
        float t = uTime * 0.05;
        vec2 warp = vec2(
            fbm(uv * 3.0 + vec2(t, t * 0.7)),
            fbm(uv * 3.0 + vec2(t * 0.8 + 5.2, t * 1.1 + 1.3))
        );
        float facets = fbm(uv * 2.0 + warp * 1.5 + t * 0.3);

        // Pointer adds a gentle lean, so the surface feels physically present
        // without turning into a cursor-tracking gimmick.
        vec2 lean = uPointer * 0.012;

        // Refraction offset. The three channels are displaced by slightly
        // different amounts — this is the prism.
        vec2 direction = vec2(facets - 0.5, warp.y - 0.5);
        vec2 offset = direction * 0.035 + lean;

        float r = texture2D(uTexture, uv + offset * (1.0 + uDispersion)).r;
        float g = texture2D(uTexture, uv + offset).g;
        float b = texture2D(uTexture, uv + offset * (1.0 - uDispersion)).b;
        vec3 color = vec3(r, g, b);

        // Specular sweep: a soft band crossing the frame on a long cycle,
        // brightest where the facet field is already steep.
        float sweep = sin((uv.x + uv.y) * 2.2 - uTime * 0.22);
        sweep = pow(max(sweep, 0.0), 12.0);
        color += vec3(1.0, 0.92, 0.78) * sweep * facets * 0.5;

        // Facet edges catch a little warm light, like a bevel.
        float edge = smoothstep(0.55, 0.85, facets);
        color += vec3(0.78, 0.66, 0.49) * edge * 0.12;

        // Vignette keeps the headline legible against the centre.
        float vignette = smoothstep(1.15, 0.35, length(vUv - 0.5));
        color *= mix(0.62, 1.0, vignette);

        gl_FragColor = vec4(color, 1.0);
    }
`;

export function initHero(canvas) {
    if (!canvas) return;

    const hero = canvas.closest('.modern-hero');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let renderer;
    try {
        renderer = new WebGLRenderer({
            canvas,
            antialias: false,     // the shader is smooth by nature; AA buys nothing
            alpha: false,
            powerPreference: 'high-performance',
        });
    } catch (error) {
        // No WebGL. The CSS parallax layers underneath stay visible.
        return;
    }

    const isSmallViewport = window.innerWidth < 768;
    const maxPixelRatio = isSmallViewport ? 1.5 : 2;
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, maxPixelRatio));
    renderer.setSize(window.innerWidth, hero ? hero.offsetHeight : window.innerHeight, false);

    const scene = new Scene();
    const camera = new OrthographicCamera(-1, 1, 1, -1, 0, 1);

    const uniforms = {
        uTexture: { value: null },
        uResolution: { value: new Vector2(1, 1) },
        uImageSize: { value: new Vector2(1, 1) },
        uPointer: { value: new Vector2(0, 0) },
        uTime: { value: 0 },
        uDispersion: { value: isSmallViewport ? 0.35 : 0.6 },
        uOctaves: { value: isSmallViewport ? 2 : 4 },
    };

    const material = new ShaderMaterial({ uniforms, vertexShader, fragmentShader });
    scene.add(new Mesh(new PlaneGeometry(2, 2), material));

    // --- sizing -----------------------------------------------------------

    function resize() {
        const width = window.innerWidth;
        const height = hero ? hero.offsetHeight : window.innerHeight;
        renderer.setSize(width, height, false);
        uniforms.uResolution.value.set(width, height);
    }

    // --- pointer ----------------------------------------------------------
    // Eased toward the target so the surface never snaps.

    const pointerTarget = new Vector2(0, 0);

    if (!reducedMotion && !isSmallViewport) {
        window.addEventListener('pointermove', (event) => {
            pointerTarget.set(
                (event.clientX / window.innerWidth) * 2 - 1,
                -((event.clientY / window.innerHeight) * 2 - 1)
            );
        }, { passive: true });
    }

    // --- visibility -------------------------------------------------------
    // Never burn GPU on a hero nobody is looking at.

    let heroVisible = true;
    if (hero && 'IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            heroVisible = entry.isIntersecting;
        }, { threshold: 0 }).observe(hero);
    }

    // --- texture ----------------------------------------------------------

    new TextureLoader().load(
        canvas.dataset.texture,
        (texture) => {
            uniforms.uTexture.value = texture;
            uniforms.uImageSize.value.set(texture.image.width, texture.image.height);
            resize();

            // Reveal the canvas and retire the CSS parallax layers only once
            // the shader actually has something to draw — avoids a black flash.
            canvas.classList.add('is-ready');
            hero?.classList.add('has-webgl');

            if (reducedMotion) {
                renderer.render(scene, camera);   // one frame, then stop
                return;
            }

            start();
        },
        undefined,
        () => {
            // Texture failed; leave the CSS layers in place.
            renderer.dispose();
        }
    );

    // --- loop -------------------------------------------------------------

    let frameId = null;
    const clockStart = performance.now();

    function frame() {
        frameId = requestAnimationFrame(frame);

        if (!heroVisible || document.hidden) return;

        uniforms.uTime.value = (performance.now() - clockStart) / 1000;
        uniforms.uPointer.value.lerp(pointerTarget, 0.04);
        renderer.render(scene, camera);
    }

    function start() {
        if (frameId === null) frame();
    }

    window.addEventListener('resize', resize, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
    initHero(document.getElementById('hero-canvas'));
});
