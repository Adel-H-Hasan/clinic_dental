/*
 TAQWEEM — Treatment -> Dental Scan -> 3D prototype.
 Standalone, zero third-party JS dependencies. This uses a small WebGL2 renderer
 for guaranteed offline playback; the production Laravel implementation can
 swap the renderer to Three.js and scroll controller to GSAP ScrollTrigger.
 The tooth is ILLUSTRATIVE and is NOT validated medical anatomy.
*/
(()=>{'use strict';
  const $=s=>document.querySelector(s),clamp=(n,a=0,b=1)=>Math.min(b,Math.max(a,n));
  const smooth=(a,b,n)=>{const x=clamp((n-a)/(b-a));return x*x*(3-2*x)};
  const phase=(a,b,c,d,n)=>smooth(a,b,n)*(1-smooth(c,d,n));
  const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
  const journey=$('#journey'),theatre=$('#theatre'),filmLayer=$('#film-layer'),filmCaption=$('#film-caption'),filmCorner=$('#film-corner');
  const video=$('#treatment-video'),still=$('#film-still'),lens=$('#lens'),veil=$('#scan-veil'),world=$('#world');
  const scanFlash=$('#scan-flash'),scanPlane=$('#scan-plane'),scanReadout=$('.scan-readout');
  const toothCopy=$('#tooth-copy'),scanCopy=$('#scan-copy'),services=$('#services');
  const footer=$('#world-footer'),gesture=$('#gesture-hint'),bar=$('#side-progress'),topBar=$('#top-progress'),rail=$('#chapter-text');
  const segments=[...document.querySelectorAll('.rail-segments i')],controls=$('#tooth-actions'),canvas=$('#tooth-canvas');
  const status=$('#live-status'),resetButton=$('#reset-tooth'),enamelStatus=$('#enamel-status'),enamelText=$('#enamel-status-text');
  let target=0,shown=0,turn=0,tilt=0,dragging=false,lastPointerX=0,lastPointerY=0,pointerId=null,live=true,frameId=0,lastRendered=0;
  let renderer=null,webglAvailable=false,videoPaused=true,journeyNear=false;
  const isMobile=()=>innerWidth<=600;
  const scrollProgress=()=>{const b=journey.getBoundingClientRect();return clamp(-b.top/Math.max(1,b.height-innerHeight));};
  function setAlpha(el,a){el.style.opacity=String(clamp(a));}
  function update(p){
    const m=smooth(.18,.40,p),filmFade=1-smooth(.385,.54,p);
    // Portrait uses a calibrated pan toward the actual monitor, not the old
    // cropped-out screen; all layers use exactly the same camera geometry.
    const camera=window.TAQWEEMPortal?.camera(m);
    if(camera){
      const position=(camera.posX*100).toFixed(2)+'% 50%';
      video.style.objectPosition=position;still.style.objectPosition=position;
      filmLayer.style.transform=`translate3d(${camera.tx.toFixed(2)}px,${camera.ty.toFixed(2)}px,0) scale(${camera.scale.toFixed(4)})`;
    }else{
      filmLayer.style.transform=`translate3d(${(-29*m).toFixed(3)}vw,${(13*m).toFixed(3)}vh,0) scale(${(1+2.4*m).toFixed(4)})`;
    }
    window.TAQWEEMPortal?.update(p,m,camera);
    setAlpha(filmLayer,filmFade);setAlpha(still,smooth(.12,.19,p));
    setAlpha(filmCaption,1-smooth(.075,.17,p));filmCaption.style.transform=isMobile()?`translateY(-${Math.round(m*10)}px)`:`translateY(calc(-50% - ${Math.round(m*36)}px))`;
    setAlpha(filmCorner,1-smooth(.12,.20,p));
    setAlpha(lens,phase(.14,.22,.33,.43,p));
    setAlpha(veil,phase(.405,.47,.505,.56,p)*.13);
    setAlpha(scanReadout,0);setAlpha(scanFlash,0);
    // v4.1: bring the REAL GLB forward while the portal is still visible.
    // The model now grows from the tunnel centre; it no longer appears at full size
    // right after the 2D core disappears. Scroll direction is fully reversible.
    setAlpha(world,smooth(.465,.608,p));
    const wc=smooth(.455,.590,p);setAlpha(toothCopy,phase(.555,.625,.697,.733,p));
    setAlpha(scanCopy,phase(.675,.705,.778,.80,p));
    const serviceOpacity=smooth(.825,.895,p);setAlpha(services,serviceOpacity);services.style.transform=`translateY(calc(-50% + ${Math.round((1-serviceOpacity)*35)}px))`;
    if(isMobile())services.style.transform=`translateY(${Math.round((1-serviceOpacity)*25)}px)`;
    const clean=smooth(.635,.765,p);
    setAlpha(scanPlane,phase(.628,.650,.745,.779,p));
    // Visual scan line tracks the molar crown instead of sweeping the entire screen.
    scanPlane.style.top=`${(33+31*clean).toFixed(2)}%`;
    if(enamelStatus){setAlpha(enamelStatus,phase(.53,.58,.89,.95,p));if(enamelText)enamelText.textContent=p<.635?((window.TaqweemLanding || {})["01 / DISCOLORED ENAMEL · CONCEPT"] || "01 / DISCOLORED ENAMEL · CONCEPT"):p<.765?((window.TaqweemLanding || {})["02 / SIMULATED CLEANING SCAN"] || "02 / SIMULATED CLEANING SCAN"):((window.TaqweemLanding || {})["03 / BRIGHT ENAMEL · CONCEPT"] || "03 / BRIGHT ENAMEL · CONCEPT");}
    const toothFallbackImg=document.querySelector('.tooth-fallback img');
    if(toothFallbackImg && !renderer){toothFallbackImg.style.filter=`sepia(${(.62*(1-clean)).toFixed(3)}) saturate(${(1.8-.8*clean).toFixed(2)}) drop-shadow(0 0 45px #3ab9bc55)`;}
    setAlpha(footer,smooth(.64,.74,p));setAlpha(controls,smooth(.58,.7,p));
    controls.style.pointerEvents=p>.6&&webglAvailable?'auto':'none';world.style.pointerEvents=p>.58&&webglAvailable?'auto':'none';
    setAlpha(gesture,1-smooth(.03,.13,p));
    bar.style.transform=`scaleY(${p.toFixed(4)})`; topBar.style.transform=`scaleX(${clamp(scrollY/(document.documentElement.scrollHeight-innerHeight||1)).toFixed(5)})`;
    const ch=p<.28?0:p<.54?1:p<.78?2:3;rail.textContent=[((window.TaqweemLanding || {})["01 / CARE"] || "01 / CARE"),((window.TaqweemLanding || {})["02 / SCAN"] || "02 / SCAN"),((window.TaqweemLanding || {})["03 / ANATOMY"] || "03 / ANATOMY"),((window.TaqweemLanding || {})["04 / SERVICES"] || "04 / SERVICES")][ch];
    segments.forEach((s,i)=>s.classList.toggle('active',i===ch));
    if(video){const shouldPlay=journeyNear && live && p<.20 && !reduced; if(shouldPlay && video.paused){video.play().catch(()=>{});} else if(!shouldPlay && !video.paused){video.pause();} videoPaused=!shouldPlay;}
    if(renderer&&journeyNear&&wc>0.01){renderer.draw({progress:p,turn,tilt,mobile:isMobile(),width:innerWidth,height:innerHeight});}
  }
  function onScroll(){target=scrollProgress();if(!frameId)frameId=requestAnimationFrame(tick);}
  function tick(){frameId=0;if(!live)return;const diff=target-shown;shown+=diff*.18;if(Math.abs(diff)<.00045)shown=target;update(shown);if(Math.abs(target-shown)>.0001||dragging)frameId=requestAnimationFrame(tick);}
  function reset(){turn=0;tilt=0;status.textContent=((window.TaqweemLanding || {})["Tooth orientation reset."] || "Tooth orientation reset.");update(shown);}
  resetButton.addEventListener('click',reset);
  canvas.addEventListener('pointerdown',e=>{if(shown<.58||e.pointerType==='mouse'&&e.button!==0)return;dragging=true;pointerId=e.pointerId;lastPointerX=e.clientX;lastPointerY=e.clientY;canvas.setPointerCapture(e.pointerId);canvas.style.cursor='grabbing';});
  canvas.addEventListener('pointermove',e=>{if(!dragging||e.pointerId!==pointerId)return;turn+=(e.clientX-lastPointerX)*.009;tilt=clamp(tilt+(e.clientY-lastPointerY)*.004,-.35,.35);lastPointerX=e.clientX;lastPointerY=e.clientY;update(shown);});
  const up=e=>{if(pointerId!==null&&e.pointerId!==pointerId)return;dragging=false;pointerId=null;canvas.style.cursor='grab';};
  canvas.addEventListener('pointerup',up);canvas.addEventListener('pointercancel',up);
  canvas.addEventListener('keydown',e=>{if(e.key==='ArrowLeft'||e.key==='ArrowRight'){e.preventDefault();turn+=(e.key==='ArrowRight'?1:-1)*.2;update(shown);}else if(e.key===((window.TaqweemLanding || {})["Home"] || "Home")){e.preventDefault();reset();}});
  if(reduced){video?.pause();still.style.opacity='1';document.body.classList.add('reduced');status.textContent=((window.TaqweemLanding || {})["Reduced-motion summary is available below the static scene."] || "Reduced-motion summary is available below the static scene.");return;}
  const journeyObserver=new IntersectionObserver(entries=>{journeyNear=entries.some(e=>e.isIntersecting);if(journeyNear && video && !video.getAttribute('src')){video.src=video.dataset.src;video.load();} if(!journeyNear)video?.pause();else onScroll();},{rootMargin:'75% 0px'});
  journeyObserver.observe(journey);
  addEventListener('scroll',onScroll,{passive:true});addEventListener('resize',()=>{renderer?.resize();onScroll();},{passive:true});
  document.addEventListener('visibilitychange',()=>{live=!document.hidden;if(!live){cancelAnimationFrame(frameId);frameId=0;video?.pause();}else{videoPaused=false;onScroll();}});
  onScroll();
  // Request the actual GLB before the monitor portal approaches its live endpoint.
  const modelObserver=new IntersectionObserver(entries=>{if(!entries.some(e=>e.isIntersecting))return;modelObserver.disconnect();loadModel().then(r=>{renderer=r;webglAvailable=true;document.body.classList.remove('model-pending');document.body.classList.add('model-live');renderer.resize();onScroll();status.textContent=((window.TaqweemLanding || {})["Interactive tooth model loaded. Drag or use arrow keys to rotate."] || "Interactive tooth model loaded. Drag or use arrow keys to rotate.");}).catch(err=>{console.warn('TAQWEEM WebGL unavailable:',err);document.body.classList.remove('model-pending');document.body.classList.add('no-webgl');status.textContent=((window.TaqweemLanding || {})["Static tooth preview is displayed because 3D rendering is unavailable."] || "Static tooth preview is displayed because 3D rendering is unavailable.");});},{rootMargin:'70% 0px'});
  modelObserver.observe(journey);

  async function loadModel(){
    const base64=window.TAQWEEM_EMBEDDED_GLB || null;
    let bytes;
    if(base64){const s=atob(base64),arr=new Uint8Array(s.length);for(let i=0;i<s.length;i++)arr[i]=s.charCodeAt(i);bytes=arr;}
    else {const res=await fetch('./assets/models/taqweem_molar_web.glb');if(!res.ok)throw new Error('GLB HTTP '+res.status);bytes=new Uint8Array(await res.arrayBuffer());}
    const view=new DataView(bytes.buffer,bytes.byteOffset,bytes.byteLength);
    if(view.getUint32(0,true)!==0x46546C67)throw new Error('Invalid GLB header');
    const jsonLen=view.getUint32(12,true);const jsonType=view.getUint32(16,true);if(jsonType!==0x4e4f534a)throw new Error('Missing glTF JSON');
    const sceneData=JSON.parse(new TextDecoder().decode(bytes.subarray(20,20+jsonLen)));
    const binChunk=20+jsonLen;if(view.getUint32(binChunk+4,true)!==0x004e4942)throw new Error('Missing glTF binary chunk');
    const binStart=binChunk+8;
    const attr=sceneData.meshes[0].primitives[0].attributes;
    const readAccessor=(id)=>{
      const acc=sceneData.accessors[id],bv=sceneData.bufferViews[acc.bufferView],components=acc.type==='VEC4'?4:acc.type==='VEC3'?3:1;
      const offset=binStart+bv.byteOffset+(acc.byteOffset||0),num=acc.count*components;
      let data;
      if(acc.componentType===5126)data=new Float32Array(bytes.buffer,bytes.byteOffset+offset,num).slice();
      else if(acc.componentType===5125)data=new Uint32Array(bytes.buffer,bytes.byteOffset+offset,num).slice();
      else if(acc.componentType===5123)data=new Uint16Array(bytes.buffer,bytes.byteOffset+offset,num).slice();
      else if(acc.componentType===5121)data=new Uint8Array(bytes.buffer,bytes.byteOffset+offset,num).slice();
      else throw new Error('Unsupported accessor type '+acc.componentType);
      return {data,acc};
    };
    const positions=readAccessor(attr.POSITION).data;
    const cols=attr.COLOR_0!==undefined?readAccessor(attr.COLOR_0).data:null;
    const index=readAccessor(sceneData.meshes[0].primitives[0].indices).data;
    const normals=new Float32Array(positions.length);
    for(let i=0;i<index.length;i+=3){const ia=index[i]*3,ib=index[i+1]*3,ic=index[i+2]*3;
      const ab0=positions[ib]-positions[ia],ab1=positions[ib+1]-positions[ia+1],ab2=positions[ib+2]-positions[ia+2];
      const ac0=positions[ic]-positions[ia],ac1=positions[ic+1]-positions[ia+1],ac2=positions[ic+2]-positions[ia+2];
      const nx=ab1*ac2-ab2*ac1,ny=ab2*ac0-ab0*ac2,nz=ab0*ac1-ab1*ac0;
      for(const j of [ia,ib,ic]){normals[j]+=nx;normals[j+1]+=ny;normals[j+2]+=nz;}
    }
    for(let i=0;i<normals.length;i+=3){const n=Math.hypot(normals[i],normals[i+1],normals[i+2])||1;normals[i]/=n;normals[i+1]/=n;normals[i+2]/=n;}
    return createRenderer({positions,index,normals,cols});
  }
  function createRenderer(mesh){
    const gl=canvas.getContext('webgl2',{antialias:true,alpha:true,powerPreference:'high-performance'});
    if(!gl)throw new Error('WebGL2 not supported');
    const vert=`#version 300 es
      precision highp float;
      in vec3 aPosition; in vec3 aNormal; in vec4 aColor;
      uniform float uYaw,uPitch,uAspect,uScale,uOffsetX,uOffsetY,uCamera;
      out vec3 vNormal;out vec3 vColor;out vec3 vPosition;out vec3 vObject;
      void main(){vec3 p=aPosition-vec3(0.0059,-0.2562,0.0);vec3 n=aNormal;
        float cy=cos(uYaw),sy=sin(uYaw),cx=cos(uPitch),sx=sin(uPitch);
        p=vec3(p.x*cy+p.z*sy,p.y,-p.x*sy+p.z*cy);
        n=vec3(n.x*cy+n.z*sy,n.y,-n.x*sy+n.z*cy);
        p=vec3(p.x,p.y*cx-p.z*sx,p.y*sx+p.z*cx);
        n=vec3(n.x,n.y*cx-n.z*sx,n.y*sx+n.z*cx);
        p=p*uScale+vec3(uOffsetX,uOffsetY,-uCamera);
        float focal=2.60; float nearZ=0.1; float farZ=60.0;
        gl_Position=vec4(p.x*focal/uAspect,p.y*focal,-(farZ+nearZ)/(farZ-nearZ)*p.z-(2.0*farZ*nearZ)/(farZ-nearZ),-p.z);
        vNormal=n;vColor=aColor.rgb;vPosition=p;vObject=aPosition;
      }`;
    const frag=`#version 300 es
      precision highp float;
      in vec3 vNormal; in vec3 vColor; in vec3 vPosition; in vec3 vObject;
      out vec4 outColor;
      uniform float uScan, uClean, uHolo;
      void main(){
        vec3 n=normalize(vNormal);vec3 light=normalize(vec3(-0.35,0.9,1.2));
        float diffuse=max(dot(n,light),0.0);float ambient=0.42;
        float rim=pow(1.0-max(dot(n,vec3(0.,0.,1.)),0.),3.5);
        float spec=pow(max(dot(reflect(-light,n),vec3(0.,0.,1.)),0.),42.0);
        // The shader changes surface color on the actual 3D object, not as a flat overlay.
        // Root surfaces retain their natural ivory color. This is a visual concept,
        // NOT a physically correct representation of dental cleaning or whitening.
        float crown=smoothstep(0.04,0.23,vObject.y);
        float sweepHeight=mix(1.35,-0.07,clamp(uClean,0.0,1.0));
        float stillStained=1.0-smoothstep(sweepHeight-0.082,sweepHeight+0.082,vObject.y);
        float grain=sin(vObject.x*24.0+sin(vObject.z*13.0)*1.3)*sin(vObject.z*18.5+vObject.y*7.0);
        float speckles=smoothstep(0.14,0.79,grain);
        float stainedAmount=crown*stillStained;
        vec3 stained= mix(vec3(0.78,0.68,0.43),vec3(0.49,0.36,0.21),speckles*0.66);
        vec3 base=mix(vColor,stained,stainedAmount*0.91);
        vec3 col=base*(ambient+diffuse*0.79)+vec3(0.94,0.99,1.)*spec*0.39+vec3(0.05,0.39,0.43)*rim*0.45;
        // Cyan light hugs the boundary of the cleaned area and advances with scroll.
        float edge=exp(-pow((vObject.y-sweepHeight)/0.075,2.0))*crown;
        col+=vec3(0.07,0.63,0.68)*edge*uScan*1.25;
        // The same real model begins as translucent, scan-lit geometry,
        // then resolves to the existing enamel material; no duplicate fake mesh.
        float contour=pow(1.0-abs(dot(normalize(n),vec3(0.,0.,1.))),2.5);
        float crossSection=pow(0.5+0.5*sin(vObject.y*78.0+vObject.x*5.0),7.0);
        float pulse=0.82+0.18*sin(vObject.z*25.0+vObject.y*9.0);
        vec3 hologram=vec3(0.035,0.62,0.76)*(0.35+1.25*contour+0.33*crossSection)*pulse;
        float solid=1.0-uHolo;
        col=mix(hologram,col,solid);
        col=pow(max(col,vec3(0.)),vec3(.93));
        float alpha=mix(0.42,1.0,solid);
        outColor=vec4(col,alpha);
      }`;
    const shader=(type,source)=>{const s=gl.createShader(type);gl.shaderSource(s,source);gl.compileShader(s);if(!gl.getShaderParameter(s,gl.COMPILE_STATUS))throw new Error(gl.getShaderInfoLog(s));return s;};
    const program=gl.createProgram(),vs=shader(gl.VERTEX_SHADER,vert),fs=shader(gl.FRAGMENT_SHADER,frag);gl.attachShader(program,vs);gl.attachShader(program,fs);gl.linkProgram(program);
    if(!gl.getProgramParameter(program,gl.LINK_STATUS))throw new Error(gl.getProgramInfoLog(program));
    gl.deleteShader(vs);gl.deleteShader(fs);gl.useProgram(program);
    const VAO=gl.createVertexArray();gl.bindVertexArray(VAO);
    const bind=(name,arr,num,type=gl.FLOAT,normalized=false)=>{const buf=gl.createBuffer();gl.bindBuffer(gl.ARRAY_BUFFER,buf);gl.bufferData(gl.ARRAY_BUFFER,arr,gl.STATIC_DRAW);const at=gl.getAttribLocation(program,name);gl.enableVertexAttribArray(at);gl.vertexAttribPointer(at,num,type,normalized,0,0);return buf;};
    const buffers=[bind('aPosition',mesh.positions,3),bind('aNormal',mesh.normals,3),bind('aColor',mesh.cols||new Uint8Array(mesh.positions.length/3*4).fill(240),4,mesh.cols?gl.UNSIGNED_BYTE:gl.UNSIGNED_BYTE,true)];
    const idx=gl.createBuffer();gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER,idx);gl.bufferData(gl.ELEMENT_ARRAY_BUFFER,mesh.index,gl.STATIC_DRAW);
    const uniforms={};for(const u of ['uYaw','uPitch','uAspect','uScale','uOffsetX','uOffsetY','uCamera','uScan','uClean','uHolo'])uniforms[u]=gl.getUniformLocation(program,u);
    gl.enable(gl.DEPTH_TEST);gl.depthFunc(gl.LEQUAL);gl.disable(gl.CULL_FACE);gl.enable(gl.BLEND);gl.blendFunc(gl.SRC_ALPHA,gl.ONE_MINUS_SRC_ALPHA);gl.clearColor(0,0,0,0);
    function resize(){const dpr=Math.min(devicePixelRatio||1,innerWidth<700?1.2:1.6);const w=Math.round(canvas.clientWidth*dpr),h=Math.round(canvas.clientHeight*dpr);if(canvas.width!==w||canvas.height!==h){canvas.width=w;canvas.height=h;}gl.viewport(0,0,w,h);}
    function draw({progress,turn,tilt,mobile,width,height}){
      resize();gl.clear(gl.COLOR_BUFFER_BIT|gl.DEPTH_BUFFER_BIT);gl.useProgram(program);gl.bindVertexArray(VAO);
      // Hold the tooth steady during the surface transformation, then turn it.
      const spin=smooth(.77,.94,progress);
      const outro=smooth(.78,.92,progress);
      // The same GLB is present during the tunnel-to-enamel materialisation.
      const reveal=smooth(.458,.690,progress);
      gl.uniform1f(uniforms.uYaw,.35+Math.PI*1.6*spin+turn);
      gl.uniform1f(uniforms.uPitch,.11+Math.sin(Math.PI*spin)*.3+tilt);
      gl.uniform1f(uniforms.uAspect,width/Math.max(1,height));
      // Start small in the CT tunnel, grow continuously, and end fully in frame.
      // Desktop v4 used 1.14/5.2: the crown was clipped at the top of the viewport.
      gl.uniform1f(uniforms.uScale,(mobile?.72:.89)*(.32+.68*reveal)*(1-.055*outro));
      gl.uniform1f(uniforms.uOffsetX,mobile?0:-1.05*outro);
      gl.uniform1f(uniforms.uOffsetY,mobile?.32*outro:0);
      gl.uniform1f(uniforms.uCamera,mobile?7.0:5.65);
      gl.uniform1f(uniforms.uHolo,1-smooth(.530,.705,progress));
      gl.uniform1f(uniforms.uScan,phase(.63,.69,.75,.79,progress));
      gl.uniform1f(uniforms.uClean,smooth(.635,.765,progress));
      gl.drawElements(gl.TRIANGLES,mesh.index.length,mesh.index instanceof Uint16Array?gl.UNSIGNED_SHORT:gl.UNSIGNED_INT,0);
    }
    canvas.addEventListener('webglcontextlost',e=>{e.preventDefault();document.body.classList.add('no-webgl');status.textContent=((window.TaqweemLanding || {})["3D context lost; static illustration shown."] || "3D context lost; static illustration shown.");});
    return {draw,resize,dispose(){buffers.forEach(x=>gl.deleteBuffer(x));gl.deleteBuffer(idx);gl.deleteVertexArray(VAO);gl.deleteProgram(program);}};
  }
})();
