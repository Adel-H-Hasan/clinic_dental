/* TAQWEEM — unified cinematic landing controller.
 * No Laravel API endpoints are called. Hero uses a single visual stage and two synchronised films.
 * One deterministic native requestAnimationFrame progress driver; fully local, no CDN dependency.
 */
(()=>{'use strict';
  const $=(q)=>document.querySelector(q), $$=(q)=>[...document.querySelectorAll(q)];
  const clamp=(x,a=0,b=1)=>Math.min(b,Math.max(a,x));
  const smooth=(a,b,p)=>{const x=clamp((p-a)/(b-a));return x*x*(3-2*x)};
  const peak=(a,b,c,d,p)=>smooth(a,b,p)*(1-smooth(c,d,p));
  const reduce=matchMedia('(prefers-reduced-motion: reduce)');
  const header=$('#global-header'),menu=$('#mobile-nav'),toggle=$('#nav-toggle');
  const stage=$('#cinema-stage'),section=$('#cinema'),out=$('#film-outside'),inside=$('#film-inside');
  const intro=$('#cinema-intro'),welcome=$('#cinema-welcome');

  const mask=$('#cinema-mask'),endwash=$('#cinema-endwash'),meter=$('#cinema-progress-fill'),sceneIndex=$('#cinema-scene-index');
  const poster=$('#cinema-poster'),bridge=$('#cinema-bridge');

  const clips={
    exterior:'./assets/video/entrance_complete_building_scrub.mp4',
    interior:'./assets/video/interior-scrub.mp4',
    poster:'./assets/img/exterior-poster.jpg',
    exteriorLength:3.84,
    interiorLength:9.82,
  };
  poster.src=clips.poster;out.poster=clips.poster;inside.poster='./assets/img/interior-poster.jpg';
  // Video decoding may take a moment. Keep the still poster visible until a real frame is available.
  out.preload='auto';inside.preload='auto';
  let exteriorReady=false, interiorReady=false;
  const showFirstFrame=()=>{
    if(out.readyState>=2&&!out.error){exteriorReady=true;poster.style.opacity=reduce.matches?'1':'0';schedule();}
  };
  const showInteriorFrame=()=>{if(inside.readyState>=2&&!inside.error){interiorReady=true;schedule();}};
  for(const name of ['loadeddata','canplay','seeked']){
    out.addEventListener(name,showFirstFrame);
    inside.addEventListener(name,showInteriorFrame);
  }
  out.pause();inside.pause();
  let target=0,shown=0,raf=0,visible=!document.hidden;
  let lastOut=-2,lastIn=-2,ioActive=false;
  const progress=()=>{const b=section.getBoundingClientRect();return clamp(-b.top/Math.max(1,b.height-innerHeight));};
  function seekIfNeeded(video,t,lastKey){
    // Let the decoder finish the pending seek; seeked schedules the latest scroll target.
    if(video.readyState<2||video.seeking||!Number.isFinite(video.duration))return lastKey;
    const v=clamp(t,0,Math.max(.01,video.duration-.04));
    if(Math.abs(v-lastKey)>.046){try{video.currentTime=v;return v}catch(_){}}
    return lastKey;
  }
  function setText(el,opacity,shift=0){opacity=clamp(opacity);el.style.opacity=opacity.toFixed(3);el.style.visibility=opacity>.012?'visible':'hidden';el.style.pointerEvents=opacity>.65?'auto':'none';el.style.translate=`0 ${Math.round(shift)}px`;}
  function render(p){
    if(reduce.matches)return;
    let exteriorTime=0,interiorTime=0,fade=0,transitionPulse=0;
    // Completed exterior -> the same entrance threshold -> interior walkthrough.
    // Building construction footage was cut from the source clip itself.
    exteriorTime=smooth(.035,.535,p)*clips.exteriorLength;
    interiorTime=smooth(.515,.955,p)*clips.interiorLength;
    // A brief doorway dip conceals the two separate sets instead of pretending the footage is one physical take.
    fade=smooth(.535,.584,p);
    transitionPulse=peak(.520,.548,.566,.607,p)*.82;
    lastOut=seekIfNeeded(out,exteriorTime,lastOut);lastIn=seekIfNeeded(inside,interiorTime,lastIn);
    inside.style.opacity=(interiorReady?fade:0).toFixed(3);
    out.style.opacity=exteriorReady?'1':'0';
    mask.style.opacity=transitionPulse.toFixed(3);
    endwash.style.opacity=smooth(.957,.996,p).toFixed(3);
    // Bridge the architecture to the editorial page without an empty cream frame.
    // Sustain the editorial bridge through the end of the sticky stage.
    // v4's 0.995..1 fade left a near-empty ivory interval in the recording.
    if(bridge){const a=smooth(.918,.970,p);bridge.style.opacity=a.toFixed(3);bridge.style.transform=`translateY(${Math.round((1-a)*26)}px)`;}

    setText(intro,1-smooth(.12,.40,p),-24*smooth(.16,.42,p));
    setText(welcome,peak(.64,.71,.958,.995,p),-12*smooth(.94,1,p));
    meter.style.transform=`scaleX(${p.toFixed(4)})`;
    sceneIndex.textContent=p<.59?'01 / 02':'02 / 02';
  }
  function tick(){raf=0;if(!visible||reduce.matches)return;shown+=(target-shown)*.18;if(Math.abs(target-shown)<.0006)shown=target;render(shown);if(Math.abs(target-shown)>.0002)raf=requestAnimationFrame(tick);}
  function schedule(){header.classList.toggle('is-scrolled',scrollY>40);if(!ioActive||reduce.matches||!visible)return;target=progress();if(!raf)raf=requestAnimationFrame(tick);}
  const mediaObserver=new IntersectionObserver(entries=>{ioActive=entries.some(e=>e.isIntersecting);if(ioActive)schedule();else{cancelAnimationFrame(raf);raf=0;}},{rootMargin:'35% 0px'});mediaObserver.observe(section);
  out.addEventListener('loadedmetadata',()=>{
    // The first H.264 frame begins just after t=0; request it explicitly for paused scrubbing.
    try { if(out.currentTime===0) out.currentTime=Math.min(.05,out.duration/2); } catch (_) {}
    schedule();
  });
  inside.addEventListener('loadedmetadata',schedule);
  // During reduced motion or failed decoding, the poster remains available behind the video.
  const fallBack=()=>{poster.style.opacity='1';out.style.display='none';inside.style.display='none'};
  out.addEventListener('error',fallBack);inside.addEventListener('error',()=>{inside.style.opacity='0';});
  // Some local HTTP servers return the full MP4 without byte-range support.
  // Chrome then reports a zero seekable range even after buffering the entire film.
  // Reuse the original bytes as a seekable Blob only for this detected condition.
  const blobUrls=[];
  function enableLocalSeeking(video,source){
    let attempted=false;
    async function repair(){
      if(attempted||reduce.matches||video.readyState<2||!Number.isFinite(video.duration))return;
      if(video.seekable.length&&video.seekable.end(video.seekable.length-1)>.1)return;
      if(!video.buffered.length||video.buffered.end(video.buffered.length-1)<video.duration-.1)return;
      attempted=true;
      try {
        const response=await fetch(source,{cache:'force-cache'});
        if(!response.ok)throw new Error('Video unavailable');
        const url=URL.createObjectURL(await response.blob());blobUrls.push(url);
        if(video===out){exteriorReady=false;lastOut=-2;poster.style.opacity='1';}
        else {interiorReady=false;lastIn=-2;}
        video.src=url;video.load();
      } catch (_) { if(video===out)fallBack(); }
    }
    ['progress','loadeddata','canplaythrough'].forEach(name=>video.addEventListener(name,repair));
    reduce.addEventListener('change',repair);
  }
  enableLocalSeeking(out,clips.exterior);enableLocalSeeking(inside,clips.interior);
  addEventListener('pagehide',event=>{if(!event.persisted)blobUrls.forEach(url=>URL.revokeObjectURL(url));});
  addEventListener('scroll',schedule,{passive:true});addEventListener('resize',schedule,{passive:true});
  document.addEventListener('visibilitychange',()=>{visible=!document.hidden;if(visible)schedule();else{cancelAnimationFrame(raf);raf=0;}});
  reduce.addEventListener('change',()=>{
    out.pause();inside.pause();poster.style.opacity=reduce.matches||!exteriorReady?'1':'0';
    if(reduce.matches){cancelAnimationFrame(raf);raf=0;setText(intro,1);if(bridge)bridge.style.opacity='0';endwash.style.opacity='0';}
    else schedule();
  });
  // Install listeners before loading, including warm-cache loads. Keep the poster on failures.
  out.style.opacity='0';inside.style.opacity='0';
  out.src=clips.exterior;inside.src=clips.interior;out.load();inside.load();
  // Deliberately one native scroll-progress driver for both video layers.
  // No CDN or competing ScrollTrigger timeline is necessary for this cinematic stage.
  // Keep navigation consistent and show where the visitor is without adding duplicated destinations.
  const navLinks=$$('.global-nav a');
  const currentSectionObserver=new IntersectionObserver(entries=>{
    const active=entries.filter(e=>e.isIntersecting).sort((a,b)=>b.intersectionRatio-a.intersectionRatio)[0];
    if(!active)return;navLinks.forEach(a=>{const match=a.getAttribute('href')==='#'+active.target.id; a.classList.toggle('is-current',match);if(match)a.setAttribute('aria-current','location');else a.removeAttribute('aria-current');});
  },{rootMargin:'-30% 0px -46% 0px',threshold:[0,.15,.5]});
  ['approach','specialists','journey','care-process','visit'].forEach(id=>{const el=document.getElementById(id);if(el)currentSectionObserver.observe(el);});
  // Menu: usable without GSAP or internet.
  function closeNav(){toggle.setAttribute('aria-expanded','false');menu.hidden=true;toggle.setAttribute('aria-label',((window.TaqweemLanding || {})["Open navigation"] || "Open navigation"));}
  toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')!=='true';toggle.setAttribute('aria-expanded',String(open));menu.hidden=!open;toggle.setAttribute('aria-label',open?((window.TaqweemLanding || {})["Close navigation"] || "Close navigation"):((window.TaqweemLanding || {})["Open navigation"] || "Open navigation"));});
  $$('#mobile-nav a').forEach(a=>a.addEventListener('click',closeNav));
  document.addEventListener('keydown',e=>{if(e.key==='Escape'){const wasOpen=toggle.getAttribute('aria-expanded')==='true';closeNav();if(wasOpen)toggle.focus();}});
  document.addEventListener('click',e=>{if(menu&&!menu.hidden&&!e.target.closest('.global-header'))closeNav();});
  // Editorial interactions: informational tabs, not fake doctor records.
  const descriptions=[
    ((window.TaqweemLanding || {})["Clinicians guide consultations, explain options and plan appropriate care."] || "Clinicians guide consultations, explain options and plan appropriate care."),
    ((window.TaqweemLanding || {})["Clinical support teams help prepare a calm and organized treatment experience."] || "Clinical support teams help prepare a calm and organized treatment experience."),
    ((window.TaqweemLanding || {})["Care coordinators support scheduling and the patient journey."] || "Care coordinators support scheduling and the patient journey.")
  ];
  $$('.specialist-row').forEach((b,i)=>b.addEventListener('click',()=>{
    $$('.specialist-row').forEach(x=>{x.classList.remove('active');x.setAttribute('aria-pressed','false')});
    b.classList.add('active');b.setAttribute('aria-pressed','true');
    $('#specialist-detail-num').textContent=`0${i+1} / ${(window.TaqweemLanding || {})["CLINIC ROLE"] || "CLINIC ROLE"}`;
    $('#specialist-detail-copy').textContent=descriptions[i];
  }));
  const stepMessages=[
    ((window.TaqweemLanding || {})["Explore the services offered by our dental team."] || "Explore the services offered by our dental team."),
    ((window.TaqweemLanding || {})["Browse real dentist profiles and choose your specialist."] || "Browse real dentist profiles and choose your specialist."),
    ((window.TaqweemLanding || {})["Choose an available date and time through secure booking."] || "Choose an available date and time through secure booking.")
  ];
  $$('.visit-step').forEach((b,i)=>b.addEventListener('click',()=>{
    $$('.visit-step').forEach(x=>{x.classList.remove('active');x.setAttribute('aria-pressed','false');});b.classList.add('active');b.setAttribute('aria-pressed','true');
    $('#visit-step-message').textContent=stepMessages[i];
  }));
  // Observed editorial items: progressive enhancement only, never hide content without JS.
  const revealObserver=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('revealed');e.target.classList.remove('reveal-pending');revealObserver.unobserve(e.target)}})},{threshold:.12});
  $$('.specialists .section-header').forEach(x=>{x.classList.add('reveal-pending');revealObserver.observe(x)});
  if(reduce.matches){poster.style.opacity='1';out.pause();inside.pause();setText(intro,1);}
  else schedule();
})();
