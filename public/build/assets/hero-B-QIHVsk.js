import{W as b,S as y,O as S,a as T,V as r,M as L,P,T as z}from"./three.module-DJqaDhL5.js";const D=`
    varying vec2 vUv;

    void main() {
        vUv = uv;
        gl_Position = vec4(position.xy, 0.0, 1.0);
    }
`,I=`
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
`;function R(o){if(!o)return;const t=o.closest(".modern-hero"),c=window.matchMedia("(prefers-reduced-motion: reduce)").matches;let i;try{i=new b({canvas:o,antialias:!1,alpha:!1,powerPreference:"high-performance"})}catch{return}const a=window.innerWidth<768,p=a?1.5:2;i.setPixelRatio(Math.min(window.devicePixelRatio,p)),i.setSize(window.innerWidth,t?t.offsetHeight:window.innerHeight,!1);const s=new y,u=new S(-1,1,1,-1,0,1),n={uTexture:{value:null},uResolution:{value:new r(1,1)},uImageSize:{value:new r(1,1)},uPointer:{value:new r(0,0)},uTime:{value:0},uDispersion:{value:a?.35:.6},uOctaves:{value:a?2:4}},w=new T({uniforms:n,vertexShader:D,fragmentShader:I});s.add(new L(new P(2,2),w));function l(){const e=window.innerWidth,m=t?t.offsetHeight:window.innerHeight;i.setSize(e,m,!1),n.uResolution.value.set(e,m)}const v=new r(0,0);!c&&!a&&window.addEventListener("pointermove",e=>{v.set(e.clientX/window.innerWidth*2-1,-(e.clientY/window.innerHeight*2-1))},{passive:!0});let f=!0;t&&"IntersectionObserver"in window&&new IntersectionObserver(([e])=>{f=e.isIntersecting},{threshold:0}).observe(t),new z().load(o.dataset.texture,e=>{if(n.uTexture.value=e,n.uImageSize.value.set(e.image.width,e.image.height),l(),o.classList.add("is-ready"),t==null||t.classList.add("has-webgl"),c){i.render(s,u);return}x()},void 0,()=>{i.dispose()});let d=null;const g=performance.now();function h(){d=requestAnimationFrame(h),!(!f||document.hidden)&&(n.uTime.value=(performance.now()-g)/1e3,n.uPointer.value.lerp(v,.04),i.render(s,u))}function x(){d===null&&h()}window.addEventListener("resize",l,{passive:!0})}document.addEventListener("DOMContentLoaded",()=>{R(document.getElementById("hero-canvas"))});
