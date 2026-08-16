import{V as s,W as c,b as u,S as h,O as d,M as v,P as f,a as m}from"./three.module-DJqaDhL5.js";const g=`
    varying vec2 vUv;
    void main() {
        vUv = uv;
        gl_Position = vec4(position.xy, 0.0, 1.0);
    }
`,p=`
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
`;class w{constructor(e){this.figure=e,this.image=e.querySelector("img"),this.hover=0,this.hoverTarget=0,this.pointer=new s(.5,.5),this.pointerTarget=new s(.5,.5),this.frameId=null,this.startedAt=performance.now()}init(){const e=document.createElement("canvas");e.className="ds-gl-canvas",e.setAttribute("aria-hidden","true");try{this.renderer=new c({canvas:e,antialias:!1,alpha:!1})}catch{return!1}const t=new u(this.image);return t.needsUpdate=!0,this.uniforms={uTexture:{value:t},uResolution:{value:new s(1,1)},uImageSize:{value:new s(this.image.naturalWidth||1,this.image.naturalHeight||1)},uPointer:{value:this.pointer},uHover:{value:0},uTime:{value:0}},this.scene=new h,this.camera=new d(-1,1,1,-1,0,1),this.scene.add(new v(new f(2,2),new m({uniforms:this.uniforms,vertexShader:g,fragmentShader:p}))),this.figure.appendChild(e),this.figure.classList.add("is-webgl"),this.resize(),this.renderOnce(),this.bindEvents(),!0}resize(){const e=this.figure.getBoundingClientRect();!e.width||!e.height||(this.renderer.setPixelRatio(Math.min(window.devicePixelRatio,1.75)),this.renderer.setSize(e.width,e.height,!1),this.uniforms.uResolution.value.set(e.width,e.height))}bindEvents(){this.figure.addEventListener("pointerenter",()=>{this.hoverTarget=1,this.start()}),this.figure.addEventListener("pointerleave",()=>{this.hoverTarget=0,this.start()}),this.figure.addEventListener("pointermove",e=>{const t=this.figure.getBoundingClientRect();this.pointerTarget.set((e.clientX-t.left)/t.width,1-(e.clientY-t.top)/t.height)},{passive:!0})}renderOnce(){this.renderer.render(this.scene,this.camera)}start(){this.frameId===null&&this.loop()}loop(){this.frameId=requestAnimationFrame(()=>this.loop()),this.hover+=(this.hoverTarget-this.hover)*.09,this.pointer.lerp(this.pointerTarget,.12),this.uniforms.uHover.value=this.hover,this.uniforms.uTime.value=(performance.now()-this.startedAt)/1e3,this.renderOnce(),this.hoverTarget===0&&this.hover<.002&&(this.uniforms.uHover.value=0,this.renderOnce(),cancelAnimationFrame(this.frameId),this.frameId=null)}}function y(n){if(!n||window.matchMedia("(prefers-reduced-motion: reduce)").matches||window.innerWidth<768)return;const e=Array.prototype.slice.call(n.querySelectorAll(".ds-gl-tile")),t=[];if(e.forEach(i=>{const r=i.querySelector("img");if(!r)return;const a=()=>{const l=new w(i);l.init()&&t.push(l)};r.complete&&r.naturalWidth?a():r.addEventListener("load",a,{once:!0})}),!!t.length){var o;window.addEventListener("resize",function(){window.clearTimeout(o),o=window.setTimeout(function(){t.forEach(function(i){i.resize(),i.renderOnce()})},150)},{passive:!0})}}document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll("[data-ds-gallery]").forEach(y)});
