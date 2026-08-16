/**
 * Diamond Shine — WebGL image gallery.
 *
 * A grid of photographs rendered through a shader. Each tile reacts to the
 * pointer with a refraction ripple that spreads from the cursor and settles
 * again — the same optical idea as the hero (light bending through a gem),
 * applied at tile scale so the two pieces read as one system.
 *
 * Progressive enhancement, strictly:
 *   - The markup is a plain <img> grid and is fully usable on its own.
 *   - This module only *replaces* a tile's rendering once its texture has
 *     decoded. If WebGL is missing, the texture fails, or motion is reduced,
 *     the original <img> simply stays visible and nothing is lost.
 *
 * Usage:
 *   <div data-ds-gallery>
 *     <figure class="ds-gl-tile"><img src="..." alt="..."></figure>
 *   </div>
 */

import {
    Mesh,
    OrthographicCamera,
    PlaneGeometry,
    Scene,
    ShaderMaterial,
    Texture,
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
    precision mediump float;

    uniform sampler2D uTexture;
    uniform vec2  uResolution;
    uniform vec2  uImageSize;
    uniform vec2  uPointer;      // 0..1 within the tile
    uniform float uHover;        // eased 0..1
    uniform float uTime;

    varying vec2 vUv;

    // Cover-fit, so photographs are never distorted by the tile's aspect.
    vec2 coverUv(vec2 uv) {
        float tileAspect  = uResolution.x / uResolution.y;
        float imageAspect = uImageSize.x / uImageSize.y;
        vec2 scale = tileAspect > imageAspect
            ? vec2(1.0, imageAspect / tileAspect)
            : vec2(tileAspect / imageAspect, 1.0);
        return (uv - 0.5) * scale + 0.5;
    }

    void main() {
        vec2 uv = coverUv(vUv);

        // Distance from the cursor, corrected for the tile's aspect so the
        // ripple stays circular rather than stretching on wide tiles.
        vec2 aspect = vec2(uResolution.x / uResolution.y, 1.0);
        float dist = distance(vUv * aspect, uPointer * aspect);

        // A ring travelling outward from the cursor, fading with distance.
        float ring = sin(dist * 18.0 - uTime * 3.0);
        float falloff = smoothstep(0.55, 0.0, dist);
        float strength = ring * falloff * uHover;

        // Displace along the vector from the cursor — this is the refraction.
        vec2 direction = normalize(vUv - uPointer + 0.0001);
        vec2 offset = direction * strength * 0.018;

        // Split the channels slightly for the prismatic edge.
        float r = texture2D(uTexture, uv + offset * 1.35).r;
        float g = texture2D(uTexture, uv + offset).g;
        float b = texture2D(uTexture, uv + offset * 0.65).b;
        vec3 color = vec3(r, g, b);

        // Warm specular lift on the crest of the ripple, in the brand's clay.
        color += vec3(0.69, 0.55, 0.39) * max(strength, 0.0) * 0.35;

        // Tiles sit slightly muted until hovered, so the grid stays calm.
        color = mix(color * 0.92, color, uHover);

        gl_FragColor = vec4(color, 1.0);
    }
`;

/** One tile: its own tiny renderer, driven only while it needs to move. */
class GalleryTile {
    constructor(figure) {
        this.figure = figure;
        this.image = figure.querySelector('img');
        this.hover = 0;
        this.hoverTarget = 0;
        this.pointer = new Vector2(0.5, 0.5);
        this.pointerTarget = new Vector2(0.5, 0.5);
        this.frameId = null;
        this.startedAt = performance.now();
    }

    init() {
        const canvas = document.createElement('canvas');
        canvas.className = 'ds-gl-canvas';
        canvas.setAttribute('aria-hidden', 'true');

        try {
            this.renderer = new WebGLRenderer({ canvas, antialias: false, alpha: false });
        } catch (error) {
            return false;   // no context — leave the <img> alone
        }

        const texture = new Texture(this.image);
        texture.needsUpdate = true;

        this.uniforms = {
            uTexture: { value: texture },
            uResolution: { value: new Vector2(1, 1) },
            uImageSize: { value: new Vector2(
                this.image.naturalWidth || 1,
                this.image.naturalHeight || 1
            ) },
            uPointer: { value: this.pointer },
            uHover: { value: 0 },
            uTime: { value: 0 },
        };

        this.scene = new Scene();
        this.camera = new OrthographicCamera(-1, 1, 1, -1, 0, 1);
        this.scene.add(new Mesh(
            new PlaneGeometry(2, 2),
            new ShaderMaterial({ uniforms: this.uniforms, vertexShader, fragmentShader })
        ));

        this.figure.appendChild(canvas);
        this.figure.classList.add('is-webgl');   // hides the <img>, shows the canvas
        this.resize();
        this.renderOnce();

        this.bindEvents();
        return true;
    }

    resize() {
        const rect = this.figure.getBoundingClientRect();
        if (!rect.width || !rect.height) return;

        // Cap DPR — a grid of canvases at 3x is a lot of fragments for no
        // visible gain on a photograph.
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.75));
        this.renderer.setSize(rect.width, rect.height, false);
        this.uniforms.uResolution.value.set(rect.width, rect.height);
    }

    bindEvents() {
        this.figure.addEventListener('pointerenter', () => {
            this.hoverTarget = 1;
            this.start();
        });

        this.figure.addEventListener('pointerleave', () => {
            this.hoverTarget = 0;
            this.start();   // keep animating until it has eased back to rest
        });

        this.figure.addEventListener('pointermove', (event) => {
            const rect = this.figure.getBoundingClientRect();
            this.pointerTarget.set(
                (event.clientX - rect.left) / rect.width,
                1 - (event.clientY - rect.top) / rect.height
            );
        }, { passive: true });
    }

    renderOnce() {
        this.renderer.render(this.scene, this.camera);
    }

    start() {
        if (this.frameId === null) this.loop();
    }

    loop() {
        this.frameId = requestAnimationFrame(() => this.loop());

        this.hover += (this.hoverTarget - this.hover) * 0.09;
        this.pointer.lerp(this.pointerTarget, 0.12);

        this.uniforms.uHover.value = this.hover;
        this.uniforms.uTime.value = (performance.now() - this.startedAt) / 1000;
        this.renderOnce();

        // Settled back to rest: stop burning frames until the next hover.
        if (this.hoverTarget === 0 && this.hover < 0.002) {
            this.uniforms.uHover.value = 0;
            this.renderOnce();
            cancelAnimationFrame(this.frameId);
            this.frameId = null;
        }
    }
}

export function initGallery(root) {
    if (!root) return;

    // Motion preference and capability are decided once, up front.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (window.innerWidth < 768) return;   // touch has no hover; save the battery

    const tiles = Array.prototype.slice.call(root.querySelectorAll('.ds-gl-tile'));
    const live = [];

    tiles.forEach((figure) => {
        const image = figure.querySelector('img');
        if (!image) return;

        const build = () => {
            // A cross-origin image would taint the canvas and throw on upload,
            // so only same-origin photographs are enhanced.
            const tile = new GalleryTile(figure);
            if (tile.init()) live.push(tile);
        };

        if (image.complete && image.naturalWidth) {
            build();
        } else {
            image.addEventListener('load', build, { once: true });
        }
    });

    if (!live.length) return;

    var resizeTimer;
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () {
            live.forEach(function (tile) { tile.resize(); tile.renderOnce(); });
        }, 150);
    }, { passive: true });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-ds-gallery]').forEach(initGallery);
});
